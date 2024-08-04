<?php
namespace xjryanse\bus\service\fixApply;

use xjryanse\logic\Arrays;
use xjryanse\logic\DataCheck;
use xjryanse\bus\service\BusService;
use xjryanse\bus\service\BusFixApplyItemService;
use xjryanse\customer\service\CustomerUserService;
use Exception;
/**
 * 
 */
trait TriggerTraits{
    /**
     * 钩子-保存前
     */
    public static function extraPreSave(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }

    /**
     * 钩子-更新前
     */
    public static function extraPreUpdate(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }

    /**
     * 钩子-删除前
     */
    public function extraPreDelete() {
        self::stopUse(__METHOD__);
    }

    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        if(!Arrays::value($data, 'fix_customer_id')){
            // 20240407：自动获取当前用户绑定的维修厂
            $userId         = session(SESSION_USER_ID);
            $customerIds    = CustomerUserService::userCustomerIdWithType($userId, 'busFix');
            $data['fix_customer_id'] = $customerIds ? $customerIds[0] : '';
        }
        $keys = ['bus_id','fix_customer_id'];
        DataCheck::must($data, $keys);
        if(Arrays::value($data, 'fix_type') == '例行保养'){
            if(!Arrays::value($data, 'sub_cate')){
                throw new Exception('子类型必须');
            }
        }
        
        self::redunFields($data, $uuid);
        if(!Arrays::value($data, 'user_id')){
            // 默认写当前用户
            $data['user_id'] = session(SESSION_USER_ID);
        }

        $itemsArr = Arrays::value($data, 'applyItems', []);
        if ($itemsArr) {
            $feeList                    = [];
            foreach ($itemsArr as $k => $v) {
                $tmpData                = $v;
                $tmpData['apply_id']    = $uuid;
                $feeList[]              = $tmpData;
            }
            BusFixApplyItemService::saveAllRam($feeList);
        }
        // 20240415:处理图片？？？
        $data['pictures']   = Arrays::value($data, 'pictures') ? $data['pictures'] : '';
        $data['file_id']    = Arrays::value($data, 'file_id') ? $data['file_id'] : '';
        // 20240504
        $data['apply_time'] = Arrays::value($data, 'apply_time') ? : date('Y-m-d H:i:s');
        // 20240801
        $data['need_appr'] = self::getInstance($v['id'])->calNeedAppr() ? 1 : 0;
        
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        self::getInstance($uuid)->feeMoneyUpdateRam();
    }

    /**
     * 钩子-更新前
     */
    public static function ramPreUpdate(&$data, $uuid) {
        self::redunFields($data, $uuid);
        $itemsArr = Arrays::value($data, 'applyItems', []);
        if ($itemsArr) {
            // 先删
            $lists = self::getInstance($uuid)->objAttrsList('busFixApplyItem');
            foreach($lists as $vi){
                BusFixApplyItemService::getInstance($vi['id'])->deleteRam();
            }
            // 再写
            $feeList                    = [];
            foreach ($itemsArr as $k => $v) {
                $tmpData                = $v;
                $tmpData['apply_id']    = $uuid;
                $feeList[] = $tmpData;
            }
            BusFixApplyItemService::saveAllRam($feeList);
        }
    }

    /**
     * 钩子-更新后
     */
    public static function ramAfterUpdate(&$data, $uuid) {
        self::getInstance($uuid)->feeMoneyUpdateRam();
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        // 删除明细
        $items = $this->objAttrsList('busFixApplyItem');
        foreach($items as $v){
            BusFixApplyItemService::getInstance($v['id'])->deleteRam();
        }
    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete() {
        self::getInstance($uuid)->feeMoneyUpdateRam();
    }
    
    protected static function redunFields(&$data, $uuid){
        $busId = Arrays::value($data, 'bus_id');
        if($busId){
            $data['dept_id'] = BusService::getInstance($busId)->fDeptId();
        }
        // TODO:20240315:根据管理人员类型，获取类型
        $userId = Arrays::value($data, 'user_id');
        if($userId){
            // todo
            $data['user_type'] = 3;
        }

        return $data;
    }
}
