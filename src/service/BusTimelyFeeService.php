<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Datetime;
use xjryanse\bus\service\BusService;
use Exception;
/**
 * 车辆按时段费用
 * monthlyManage:车辆管理月费
 * 
 */
class BusTimelyFeeService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    use \xjryanse\traits\FinanceSourceModelTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusTimelyFee';

//    use \xjryanse\bus\service\fix\FieldTraits;
//    use \xjryanse\bus\service\fix\TriggerTraits;
//    use \xjryanse\bus\service\fix\ListTraits;
//    use \xjryanse\bus\service\fix\DoTraits;
//    use \xjryanse\bus\service\fix\PaginateTraits;

    /**
     * 额外详情
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    return $lists;
                }, true);
    }

    /**
     * 车辆管理费用
     * @param type $busIds      车辆
     * @param type $prize       费用
     * @param type $yearmonth   管理费
     */
    public static function initBusManageFee($busIds, $prize, $yearmonth){
        if(!$yearmonth){
            $yearmonth = date('Y-m');
        }
        $feeType = 'monthlyManage';
        self::preInitDataCheck($feeType, $busIds, $yearmonth);
        if(!is_array($busIds)){
            $busIds = [$busIds];
        }
        foreach($busIds as $busId){
            self::busMonthSaveRam($feeType, $busId, $yearmonth, $prize);
        }
        return true;
    }
    /**
     * 车辆维度，月份保存
     * @param type $feeType
     * @param type $busId
     * @param type $yearmonth
     */
    protected static function busMonthSaveRam($feeType, $busId, $yearmonth, $prize, $data = []){
        $data['bus_id']     = $busId;
        $data['start_time'] = Datetime::monthStartTime($yearmonth);
        $data['end_time']   = Datetime::monthEndTime($yearmonth);
        $data['fee_type']   = $feeType;
        $data['prize']      = $prize;

        return self::saveRam($data);
    }
    
    /**
     * 初始化时，验证是否有干扰项存在
     * @param type $feeType
     * @param type $busIds
     * @param type $yearmonth
     */
    protected static function preInitDataCheck($feeType, $busIds,$yearmonth){
        $startTime  = Datetime::monthStartTime($yearmonth);
        $endTime    = Datetime::monthEndTime($yearmonth);
        $con    = [];
        // 结束时间在给定开始时间之后，开始时间在给定结束时间之后
        $con[]  = ['end_time','>=',$startTime];
        $con[]  = ['start_time','<=',$endTime];
        $con[]  = ['bus_id','in',$busIds];
        $con[]  = ['fee_type','=',$feeType];
        
        $hasCount = self::where($con)->count();
        if($hasCount){
            throw new Exception('已有'.$hasCount.'条记录，请先删除');
        }
    }


}
