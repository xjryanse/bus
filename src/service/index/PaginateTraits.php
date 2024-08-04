<?php
namespace xjryanse\bus\service\index;

use xjryanse\logic\Arrays;
use xjryanse\logic\DataCheck;
use Exception;
use xjryanse\bus\service\BusDriverAbilityService;
use xjryanse\prize\service\PrizeGroupService;
use xjryanse\prize\service\PrizeRuleService;

/**
 * 触发复用
 */
trait PaginateTraits{
    /**
     * 20231128:用于后台针对现有车辆进行抽成率配置
     * 
     * @param type $data
     * @param type $uuid
     */
    public static function paginateForRate($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false) {

        $con[] = ['owner_type','=','self'];
        $con[] = ['status','=',1];
        $order = 'passenger_max desc';
        $res = self::paginateX($con, $order, $perPage, $having, $field, $withSum);
        
        $groupCate  = 'driverSalaryRate';
        $groupIds   = PrizeGroupService::cateToIds($groupCate);
        // 20231128:数组
        $arr        = PrizeRuleService::getPrizeKeyCountByGroupId($groupIds);

        foreach($res['data'] as &$v){
            $v['prizeKey']          = 'DRB_'.$v['id'];
            $v['prizeRuleCount']    = Arrays::value($arr, $v['prizeKey']) ? : 0;
        }

        return $res;

    }

    /**
     * 20231128:用于提取当前驾驶员管理的车辆信息
     * @param type $data
     * @param type $uuid
     */
    public static function paginateDriverBus($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false) {
        $driverId   = session(SESSION_USER_ID);
        $busIds     = BusDriverAbilityService::driverBusIds($driverId);
        $con[]      = ['id','in',$busIds];

        $res = self::paginateX($con, $order, $perPage, $having, $field, $withSum);

        return $res;
    }

}
