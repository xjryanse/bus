<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\approval\interfaces\ApprovalOutInterface;
use xjryanse\bus\service\BusFixService;
use xjryanse\bus\service\BusFixItemService;
use xjryanse\logic\Arrays;
use Exception;
/**
 * 车辆维修申请
 */
class BusFixApplyService extends Base implements MainModelInterface, ApprovalOutInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\MainModelRecentTrait;

    use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    use \xjryanse\traits\FinanceSourceModelTrait;
    use \xjryanse\approval\traits\ApprovalOutTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusFixApply';
    //直接执行后续触发动作
    protected static $directAfter = true;
    use \xjryanse\bus\service\fixApply\FieldTraits;
    use \xjryanse\bus\service\fixApply\TriggerTraits;
    use \xjryanse\bus\service\fixApply\ApprovalTraits;
    use \xjryanse\bus\service\fixApply\CalTraits;
    
//    use \xjryanse\bus\service\fixApply\ListTraits;
//    use \xjryanse\bus\service\fixApply\DoTraits;
//    use \xjryanse\bus\service\fixApply\PaginateTraits;

    /**
     * 额外详情
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    foreach($lists as &$v){
                        $v['applyItems'] = self::getInstance($v['id'])->objAttrsList('busFixApplyItem');
                        // 20240801:计算是否需审批
                        $v['calNeedAppr'] = self::getInstance($v['id'])->calNeedAppr() ? 1 : 0;
                    }

                    return $lists;
                }, true);
    }
    
    public function info(){
        $infoRaw    = $this->get();
        $info       = $this->pushDynDataList($infoRaw);
        $info['applyItems'] = self::getInstance($v['id'])->objAttrsList('busFixApplyItem');
        return $info;
    }
    
    
    public function feeMoneyUpdateRam() {
        $data['prize'] = $this->calFeeMoney();
        return $this->doUpdateRam($data);
    }

    
    /**
     * 写入维修表（确认后）
     */
    public function toFix(){
        $con[] = ['apply_id','=',$this->uuid];
        if(BusFixService::where($con)->count()){
            throw new Exception('单据已归档，不可重复操作');
        }
        $info = $this->get();
        $keys = ['bus_id','fix_customer_id','bus_mile','fix_type','describe'];
        $data = Arrays::getByKeys($info, $keys);
        $data['fault_cate'] = $info['fix_type'];
        $data['fix_time']   = $info['apply_time'];
        $data['apply_id']   = $this->uuid;
        
        $fixId = BusFixService::saveGetIdRam($data);
        // 20240408
        $lists = $this->objAttrsList('busFixApplyItem');
        $arr = [];
        foreach($lists as $v){
            $keys1 = ['item_name','number','unit','unit_prize','total_prize'];
            $tmp = Arrays::getByKeys($v, $keys1);
            $tmp['fix_id'] = $fixId;
            $arr[] = $tmp;
        }
        BusFixItemService::saveAllRam($arr);
        // 更新当前isOk
        $this->doUpdateRam(['is_ok'=>1]);

        return true;
    }
    /**
     * 
     */
    public function cancelFix(){
        $con[] = ['apply_id','=',$this->uuid];
        $fixId = BusFixService::where($con)->value('id');
        $lists = BusFixService::getInstance($fixId)->objAttrsList('busFixItem');
        foreach($lists as $v){
            // 加保险
            if($v['fix_id'] == $fixId){
                BusFixItemService::getInstance($v['id'])->deleteRam();
            }
        }
        BusFixService::getInstance($fixId)->deleteRam();
        
        // 更新当前isOk
        $this->doUpdateRam(['is_ok'=>0]);

        return true;
        
    }
    /**
     * 20240415:更新并上报完工
     */
    public function doUpdateAndFinish($param){
        $this->updateRam($param);
        
        $paramRaw = [];
        // 'apply_id','operate_type','opinion'
        $paramRaw['apply_id'] = $this->uuid;
        $paramRaw['operate_type'] = 'BFinish';
        
        BusFixApplyOperateService::doOperate($paramRaw);
        return $this->uuid;
    }

    
}
