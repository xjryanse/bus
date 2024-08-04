<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
use xjryanse\system\service\SystemCondService;
/**
 * w_bus_fix_apply_operate
 */
class BusFixApplyOperateService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;


    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusFixApplyOperate';
    //直接执行后续触发动作
    protected static $directAfter = true;
    use \xjryanse\bus\service\fixApplyOperate\TriggerTraits;
    /**
     * 
     * operate_type:甲方A；乙方B
     * AToPrize 甲方提交需求给乙方
     * BPrizeFinish 乙方报价完成
     * APrizeCheck 甲方确认报价
     * BAcceptOrder 乙方接单
     * BFinishOrder 乙方完工
     * AVerify 甲方验收
     * ASettle 甲方给乙方结算
     * @param type $param
     * @return type
     */
    public function doOperate($paramRaw){
        $param = Arrays::value($paramRaw, 'table_data') ? : $paramRaw;
        $keys = ['apply_id','operate_type','opinion'];
        $data = Arrays::getByKeys($param, $keys);

        $operateKey = Arrays::value($param, 'operate_type');
        $dataId     = Arrays::value($param, 'apply_id');
        // 20240415：加入了未达成判断抛出异常中断流程
        SystemCondService::isReachByItemKey('busFixApply', $operateKey, $dataId, $param);

        $data['direction']              = Arrays::value($param, 'direction') ? : 1;
        $data['operate_user']           = session(SESSION_USER_ID);
        $data['operate_wepub_openid']   = session(SESSION_OPENID);
        $data['operate_time']           = date('Y-m-d H:i:s');

        return self::saveRam($data);
    }
    
}
