<?php
namespace xjryanse\bus\service\washLog;

use xjryanse\bus\service\BusDriverAbilityService;
/**
 * 触发复用
 */
trait PaginateTraits{


    /**
     * 20231128:用于提取当前驾驶员管理的记录
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
