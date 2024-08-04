<?php
namespace xjryanse\bus\service\fix;

use xjryanse\logic\Arrays;
use xjryanse\bus\service\BusDriverAbilityService;
/**
 * 触发复用
 */
trait PaginateTraits{
    /**
     * 20231128:用于提取当前驾驶员上报的记录
     * @param type $data
     * @param type $uuid
     */
    public static function paginateDriverMe($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false) {
        $con[] = ['driver_id','=',session(SESSION_USER_ID)];
        
        $res = self::paginateX($con, $order, $perPage, $having, $field, $withSum);

        return $res;
    }

    /**
     * 20231128:用于提取当前驾驶员上报的记录
     * @param type $data
     * @param type $uuid
     */
    public static function paginateDriverBus($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false) {
        $driverId = session(SESSION_USER_ID);
        $busIds = BusDriverAbilityService::driverBusIds($driverId);
        $con[]  = ['bus_id','in',$busIds];

        $res = self::paginateX($con, $order, $perPage, $having, $field, $withSum);

        return $res;
    }
}
