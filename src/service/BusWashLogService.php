<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Debug;
use xjryanse\logic\Cachex;
use xjryanse\logic\Arrays2d;
use think\Db;

/**
 * 
 */
class BusWashLogService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;
    use \xjryanse\traits\MainModelRecentTrait;
    
    use \xjryanse\traits\MainStaticsTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusWashLog';

    use \xjryanse\bus\service\washLog\DoTraits;
    use \xjryanse\bus\service\washLog\PaginateTraits;
    use \xjryanse\bus\service\washLog\FieldTraits;
    use \xjryanse\bus\service\washLog\MeTraits;
    use \xjryanse\bus\service\washLog\TriggerTraits;
    
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    foreach ($lists as &$v) {
                        // 是否本人的报销单
                        $v['driverIsMe'] = $v['driver_id'] == session(SESSION_USER_ID) ? 1:0;
                    }

                    return $lists;
                }, true);
    }
    
    /**
     * 根据日期获取车辆日洗车记录
     * @param type $date
     * @return type
     */
    public static function getWashByDate($date) {
        $busTable = BusService::getTable();
        $washLogTable = self::getTable();

        $companyId = session('scopeCompanyId');
        $sql = "SELECT a.id,a.licence_plate,date( b.wash_time ) as belong_date,b.has_wash 
            FROM " . $busTable . " AS a
                LEFT JOIN (select * from " . $washLogTable . " where date( wash_time ) = '" . $date . "') AS b ON a.id = b.bus_id 
            WHERE 
                a.company_id='" . $companyId . "' and a.status = 1 and a.owner_type = 'self' and (date( b.wash_time ) = '" . $date . "' or b.wash_time is null) ORDER BY has_wash desc,passenger_max DESC";
        Debug::debug('getWashByDate的$sql', $sql);
        $res = Db::query($sql);
        return $res;
    }

    /*
     * 洗车记录数组
     */

    public static function washLogsArr($busIds, $minTime, $maxTime) {
        // 趟检单24小时有效
        $cone[] = ['bus_id', 'in', $busIds];
        $cone[] = ['wash_time', '>=', $minTime];
        $cone[] = ['wash_time', '<', $maxTime];
        $Logs = self::lists($cone);
        $arr = $Logs ? $Logs->toArray() : [];
        /*         * ********** */
        return $arr;
    }

    /**
     * 日洗车上报数组：缓存查询
     * @param type $date
     * @return type
     */
    public static function dateWashLogArr($date) {
        $cacheKey = __METHOD__ . $date;
        return Cachex::funcGet($cacheKey, function() use ($date) {
                    $con[] = ['wash_time', '>=', date('Y-m-d 00:00:00', strtotime($date))];
                    $con[] = ['wash_time', '<=', date('Y-m-d 23:59:59', strtotime($date))];
                    $lists = self::lists($con, 'wash_time desc');
                    $listsArr = $lists ? $lists->toArray() : [];
                    return $listsArr;
                }, true, 60);
    }

    /**
     * 20220919 车辆日末次洗车
     * @param type $busId
     * @param type $time
     * @return type
     */
    public static function busDailyLastWashLog($busId, $time = '') {
        if (!$time) {
            $time = date('Y-m-d H:i:s');
        }
        $date = date('Y-m-d', strtotime($time));
        $todayWashArr = self::dateWashLogArr($date);
        $con[] = ['bus_id', '=', $busId];
        $lastWash = Arrays2d::listFind($todayWashArr, $con);

        return $lastWash;
    }

    public static function staticsBusByMonth($con = [], $orderBy = '') {
        return self::staticsBus('month', $con, $orderBy);
    }

    /**
     * 按年统计驾驶员信息
     * @param type $con
     * @return type
     */
    public static function staticsBusByYear($con = [], $orderBy = "") {
        return self::staticsBus('year', $con, $orderBy);
    }

    /**
     * 20220922:按车辆聚合查询
     * @param type $staticsBy
     * @param type $con
     * @param type $orderBy
     * @return type
     */
    protected static function staticsBus($staticsBy = 'date', $con = [], $orderBy = '') {
        //调用公共聚合查询逻辑
        return self::commStaticsTimeGroup($staticsBy, $con, function($con, $groupField, $orderByStr) {
                    $data = self::where($con)
                            ->group("company_id,bus_id,date_format( `wash_time`, '" . $groupField . "' ) " . $orderByStr)
                            ->field("company_id,bus_id,
                                    date_format( `wash_time`, '" . $groupField . "' ) as belongTime,
                                    count(*) as washCount")
                            ->select();
                    return $data ? $data->toArray() : [];
                }, $orderBy);
    }

}
