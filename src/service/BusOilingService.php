<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\finance\interfaces\StaffFeeOutInterface;

use app\third\service\ThirdOilLiuListService;
use xjryanse\logic\Datetime;
use xjryanse\logic\DataCheck;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;
use xjryanse\logic\Cachex;
use think\Db;
use Exception;

/**
 * 
 */
class BusOilingService extends Base implements MainModelInterface, StaffFeeOutInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\MainStaticsTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    use \xjryanse\traits\FinanceSourceModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusOiling';
    //直接执行后续触发动作
    protected static $directAfter = true;

    use \xjryanse\bus\service\oiling\DoTraits;
    use \xjryanse\bus\service\oiling\PaginateTraits;
    use \xjryanse\bus\service\oiling\FieldTraits;
    use \xjryanse\bus\service\oiling\ListTraits;
    use \xjryanse\bus\service\oiling\TriggerTraits;
    use \xjryanse\bus\service\oiling\CalTraits;
    use \xjryanse\bus\service\oiling\StaticsTraits;
    use \xjryanse\bus\service\oiling\StaffFeeTraits;

    /**
     * 
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use ($ids) {
                    $conNext[] = ['last_id', 'in', $ids];
                    $nextArr = self::where($conNext)->column('id', 'last_id');
                    $oilLiuCount = ThirdOilLiuListService::groupBatchCount('number', array_column($lists, 'source_id'));

                    foreach ($lists as &$v) {
                        // 20231205:是否有公里数
                        $v['hasKilometer']  = $v['kilometer'] ? 1 : 0;
                        $v['hasMileId']     = Arrays::value($v, 'mile_id') ? 1 : 0;
                        //是否刚添加的记录,4小时内
                        // $v['isRecent'] = time() > strtotime($v['create_time']) && (time() - strtotime($v['create_time'])) < 3600 * 4 ? 1 : 0;
                        $v['isRecent'] = Datetime::isRecent($v['create_time'], 3600 * 4) ? 1 : 0;
                        //当前登录用户是否驾驶员本人（控制前端删除按钮是否显示）
                        $v['driverIsMe'] = $v['driver_id'] == session(SESSION_USER_ID) ? 1 : 0;
                        // 20220929:单条数据才出详情
                        $v['hasLast'] = $v['last_id'] ? 1 : 0;
                        $v['next_id'] = Arrays::value($nextArr, $v['id']);
                        $v['hasNext'] = $v['next_id'] ? 1 : 0;

                        if (!is_array($ids) || (is_array($ids) && count($ids) == 1)) {
                            $con = [];
                            $con[] = ['id', '=', $v['last_id']];
                            $info = $v['last_id'] ? self::where($con)->find() : [];

                            $v['hasLast'] = $info ? 1 : 0;
                            // 上次加油时间
                            $v['lastOilTime'] = Arrays::value($info, 'time');
                            // 上次是否加满
                            $v['isLastFull'] = Arrays::value($info, 'is_full', 0);
                            $v['lastVolume'] = Arrays::value($info, 'number', 0);
                            $v['lastPrize'] = Arrays::value($info, 'prize', 0);
                            $v['lastKilometer'] = Arrays::value($info, 'kilometer', 0);
                            $v['lastGpsMile'] = Arrays::value($info, 'gps_mile', 0);
                            // 行驶时长
                            $v['hoursDiff'] = $v['lastOilTime'] ? strtotime($v['time']) - strtotime($v['lastOilTime']) : 0;
                            // 行驶公里数
                            $v['milesDiff'] = $v['kilometer'] && $v['lastKilometer'] ? $v['kilometer'] - $v['lastKilometer'] : 0;
                            // 行驶公里数
                            $v['gpsMilesDiff'] = $v['gps_mile'] && $v['lastGpsMile'] ? $v['gps_mile'] - $v['lastGpsMile'] : 0;
                            // 每公里油耗 = 本次加油升数 / 行驶公里数
                            $v['perMileVolume'] = $v['is_full'] && $v['milesDiff'] ? ($v['isLastFull'] ? round($v['number'] / $v['milesDiff'], 3) : '上次未加满无法分析') : ($v['milesDiff'] ? '本次未加满无法分析' : '无公里数偏差数据不可分析');
                            // 每公里油单价
                            $v['perMilePrize'] = $v['is_full'] && $v['milesDiff'] ? ($v['isLastFull'] ? round($v['prize'] / $v['milesDiff'], 2) : '上次未加满无法分析') : ($v['milesDiff'] ? '本次未加满无法分析' : '无公里数偏差数据不可分析');
                        }
                        $v['milesDiffNew'] = $v['is_full'] && $v['kilometer'] && $v['last_full_kilometer'] > 0 ? $v['kilometer'] - $v['last_full_kilometer'] : '';
                        $v['gpsMilesDiffNew'] = $v['is_full'] && $v['gps_mile'] && $v['last_full_gps_mile'] > 0 ? round($v['gps_mile'] - $v['last_full_gps_mile'], 2) : '';
                        // 20221115:优先提取驾驶员上报的公里数，当驾驶员未上报公里数，则提取GPS公里数
                        $mileDiff = $v['milesDiffNew'] ?: $v['gpsMilesDiffNew'];
                        $v['mileDiff'] = $mileDiff;
                        //每公里油耗（升数）
                        $v['perMileVolumeNew'] = $v['is_full'] && $mileDiff ? round($v['accum_number'] / $mileDiff, 3) : '';
                        //每公里油钱
                        $v['perMilePrizeNew'] = $v['is_full'] && $mileDiff ? round($v['accum_prize'] / $mileDiff, 3) : '';
                        // 平均单价
                        $v['avgUnitPrize'] = $v['is_full'] && $v['accum_number'] > 0 ? round($v['accum_prize'] / $v['accum_number'], 3) : '';
                        // 20221115 加油机接口数据
                        $v['oilLiuCount'] = Arrays::value($oilLiuCount, $v['source_id'], 0);
                    }

                    return $lists;
                },true);
    }

    /**
     * 匹配到了末次加油id
     */
    public static function matchLastId($busId, $number, $prize) {
        $con[] = ['bus_id', '=', $busId];
        $info = self::where($con)->order('id desc')->find();
        if ($info['driver_id']) {
            return false;
        }
        if ($info['number'] != $number || $info['prize'] != $prize) {
            return false;
        }
        return $info['id'];
    }

    /**
     * 钩子-保存前，
     * 准备弃用了-20231114
     */
    public static function extraPreSave(&$data, $uuid) {
        $keys = ['bus_id'];
        DataCheck::must($data, $keys);
        if ($data['number'] > 1000) {
            throw new Exception('加油升数太大');
        }
        if ($data['prize'] > 10000) {
            throw new Exception('加油金额太大');
        }
        //加油人
        if (!Arrays::value($data, 'driver_id')) {
            $data['driver_id'] = session(SESSION_USER_ID);
        }
        // 加油时间
        if (!Arrays::value($data, 'time')) {
            $data['time'] = date('Y-m-d H:i:s');
        }
        // 20220929
        if (!Arrays::value($data, 'unit_price') && (Arrays::value($data, 'prize') && Arrays::value($data, 'number'))) {
            $data['unit_price'] = round(Arrays::value($data, 'prize') / Arrays::value($data, 'number'), 2);
        }
        // 新增时，写入末次加油id
        $data['last_id'] = self::lastOilingId($data['bus_id']);
    }

    /**
     * 钩子-保存后
     */
    public static function extraAfterSave(&$data, $uuid) {
        $dataUpd = self::getInstance($uuid)->calLastFullData();
        $con[] = ['id', '=', $uuid];
        self::where($con)->update($dataUpd);
    }

    /**
     * 钩子-更新前
     */
    public static function extraPreUpdate(&$data, $uuid) {
        // 20220929
        if (Arrays::value($data, 'prize') && Arrays::value($data, 'number')) {
            $data['unit_price'] = round(Arrays::value($data, 'prize') / Arrays::value($data, 'number'), 2);
        }
    }

    /**
     * 钩子-更新后
     */
    public static function extraAfterUpdate(&$data, $uuid) {
        // 20220929
        if (Arrays::value($data, 'bus_id')) {
            $data['unit_price'] = round(Arrays::value($data, 'prize') / Arrays::value($data, 'number'), 2);
        }

        $info = self::getInstance($uuid)->get();
        // 20230318：循环更新该车时间在当前加油时间之后的所有加油记录
        $conBus[] = ['bus_id', '=', $info['bus_id']];
        $conBus[] = ['time', '>=', $info['time']];
        $ids = self::where($conBus)->order('time')->column('id');

        foreach ($ids as $id) {
            //20221116
            $dataUpd = self::getInstance($id)->calLastFullData();
            $con = [];
            $con[] = ['id', '=', $id];
            self::where($con)->update($dataUpd);
        }
    }

    /**
     * 时间范围提取统计数据
     */
    public static function scopeStatics($startTime, $endTime) {
        // 【1】提取公里数偏差值
        $mileBusArr = self::busMileDiffArr($startTime, $endTime);
        $mileBusObj = Arrays2d::fieldSetKey($mileBusArr, 'id');
        // 【2】提取加油累计值
        $oilBusArr = self::busOilAccArr($startTime, $endTime);
        // 【3】计算百公里油耗
        foreach ($oilBusArr as &$bus) {
            $bus['bus_id'] = $bus['id'];
            $bus = array_merge($bus, $mileBusObj[$bus['id']]);
            // 公里偏差值
            $mileDiff = $bus['mileDiff'];
            //每公里油耗
            $bus['perKilometerVolume'] = $mileDiff ? round($bus['oilVolume'] / $mileDiff, 3) : 0;
            //每公里油钱
            $bus['perKilometerPrize'] = $mileDiff ? round($bus['oilPrize'] / $mileDiff, 3) : 0;
            // 2022-11-15 百公里油耗
            $bus['100KmVolume'] = round($bus['perKilometerVolume'] * 100, 1);
            // 2022-11-15 百公里油钱
            $bus['100KmPrize'] = round($bus['perKilometerPrize'] * 100, 1);
        }

        return $oilBusArr;
    }

    /**
     * 封装用于对比的车辆数组
     * @return type
     */
    public static function busArrForStatics() {
        $busCon[] = ['owner_type', '=', 'self'];
        // 20230401:TODO会过滤掉一些有数据的历史车辆
        $busCon[] = ['status', '=', 1];
        $busList = BusService::where($busCon)->order('passenger_max desc')->field('id,current_driver')->select();
        $busArr = $busList ? $busList->toArray() : [];
        return $busArr;
    }

    /**
     * 2022-11-27：根据时间范围，提取记录
     * @param type $startTime
     * @param type $endTime
     * @param type $con         附加条件，一般用于剔除首次加油的数据
     * @return type
     */
    public static function timeScopeArr($startTime, $endTime, $con = []) {
        $con[] = ['time', '>=', $startTime];
        $con[] = ['time', '<=', $endTime];
        $lists = self::where($con)->field('id,bus_id,driver_id,kilometer,time,gps_mile,prize,number')->order('time desc')->select();
        $listsArr = $lists ? $lists->toArray() : [];
        return $listsArr;
    }

    /**
     * 获取公里偏差值
     * 结束时间最后-开始时间前一个
     */
    public static function busMileDiffArr($startTime, $endTime) {
        //查结束时间，最后一次加油
        $thisOilArr = self::busLastOilArr($endTime);
        //查开始时间，最后一次加油
        $preOilArr = self::busLastOilArr($startTime);
        // 查首次加油，用于比较
        $firstOilArr = self::busFirstOilArr();
        // 用于比较的加油数组
        $busArr = self::busArrForStatics();
        foreach ($busArr as &$bus) {
            //【1】首次加油的数据
            $bus['firstKilometer'] = isset($firstOilArr[$bus['id']]) ? $firstOilArr[$bus['id']]['kilometer'] : 0;
            $bus['firstGpsMile'] = isset($firstOilArr[$bus['id']]) ? $firstOilArr[$bus['id']]['gps_mile'] : 0;
            // 末次上报时间
            $bus['firstUplTime'] = isset($firstOilArr[$bus['id']]) ? $firstOilArr[$bus['id']]['time'] : '';

            //【2】上月底公里数$preOilArr
            $bus['lastKilometer'] = isset($preOilArr[$bus['id']]) ? $preOilArr[$bus['id']]['kilometer'] : 0;
            $bus['lastGpsMile'] = isset($preOilArr[$bus['id']]) ? $preOilArr[$bus['id']]['gps_mile'] : 0;
            // 末次上报时间
            $bus['lastUplTime'] = isset($preOilArr[$bus['id']]) ? $preOilArr[$bus['id']]['time'] : '';

            //【3】本月底公里数 当本月公里无数据时，沿用上月末次数据
            $bus['thisKilometer'] = isset($thisOilArr[$bus['id']]) ? $thisOilArr[$bus['id']]['kilometer'] : 0;
            $bus['thisGpsMile'] = isset($thisOilArr[$bus['id']]) ? $thisOilArr[$bus['id']]['gps_mile'] : 0;
            // 本次上报时间
            $bus['thisUplTime'] = isset($thisOilArr[$bus['id']]) ? $thisOilArr[$bus['id']]['time'] : '';
            // 【4】计算公里偏差值
            // 前序公里：上月有公里取上月公里，上月无公里取首次
            // $preKilo    = $bus['lastKilometer'] ? : $bus['firstKilometer'];
            // $preGps     = $bus['lastGpsMile'] ? : $bus['firstGpsMile'];
            $preKilo = $bus['lastKilometer'];
            $preGps = $bus['lastGpsMile'];
            // 本月有公里，本月-前序；无则0
            $bus['kiloDiff'] = $bus['thisKilometer'] && $preKilo ? round($bus['thisKilometer'] - $preKilo, 2) : 0;
            $bus['gpsDiff'] = $bus['thisGpsMile'] && $preGps ? round($bus['thisGpsMile'] - $preGps, 2) : 0;
            // 用于核算油耗的公里数：
            // 当有手报公里数时，以手报公里数来计算油耗，否则，以GPS采集的公里数来计算油耗
            $mileDiff = $bus['kiloDiff'] && $bus['kiloDiff'] > 0 ? $bus['kiloDiff'] : $bus['gpsDiff'];
            $bus['mileDiff'] = round($mileDiff, 1);

            $bus['$endTime'] = $endTime;
            $bus['$thisOilArr'] = $thisOilArr[$bus['id']];
            $bus['$preOilArr'] = $preOilArr[$bus['id']];
        }
        return $busArr;
    }

    public static function busOilAccArr($startTime, $endTime) {
        // 提取首次id，用于剔除
        $firstIds = self::busFirstOilIds();

        $con[]      = ['id', 'not in', $firstIds];
        $listsArr   = self::timeScopeArr($startTime, $endTime, $con);
        // 用于比较的加油数组
        $busArr     = self::busArrForStatics();

        foreach ($busArr as &$bus) {
            $cone = [];
            $cone[] = ['bus_id', '=', $bus['id']];
            // 有效加油次数
            $bus['effOilCounts']    = count(Arrays2d::listFilter($listsArr, $cone));
            // 加油升数（需剔除第一条记录）
            $bus['oilVolume']       = round(array_sum(array_column(Arrays2d::listFilter($listsArr, $cone), 'number')), 2);
            // 加油金额（需剔除第一条记录）
            $bus['oilPrize']        = round(array_sum(array_column(Arrays2d::listFilter($listsArr, $cone), 'prize')), 2);
        }
        return $busArr;
    }

    /**
     * 2022-11-27:各车截止指定时间，最后一次加油记录
     */
    public static function busLastOilArr($endTime = '', $scope = 'month') {
        if (!$endTime) {
            $endTime = date('Y-m-d H:i:s');
        }
        //提取各车的最近一条加油记录
        $conLast[] = ['time', '<', $endTime];
        // 20230401：只提取日期
        if ($scope == 'month') {
            $conLast[] = ['time', '>', date('Y-m-01 00:00:00', strtotime('-1 month', strtotime($endTime)))];
        }
        // TODO:这个有问题
        $times = self::where($conLast)->group('bus_id')->column('max(time)');
        if(!$times){
            return [];
        }
        //根据id，提取数据
        $conL[] = ['time', 'in', $times];
        // dump($conL);
        $listArr = self::where($conL)->order('time desc')
                ->column('id,bus_id,driver_id,kilometer,gps_mile,time,number,prize', 'bus_id');

        return $listArr;
    }

    /**
     * 首次加油的记录id
     */
    public static function busFirstOilIds($startTime = '') {
        $conFirst = [];
        if ($startTime) {
            $conFirst[] = ['time', '>=', $startTime];
        }
        //提取各车的首次
        $firstIds = self::where($conFirst)->group('bus_id')->column('min(id)');
        return $firstIds;
    }

    /**
     * 2022-11-27：各车从指定时间开始，首次加油记录
     * @param type $startTime
     * @return type
     */
    public static function busFirstOilArr($startTime = '') {
        //提取各车的首次加油记录id
        $firstIds = self::busFirstOilIds($startTime);
        //根据id，提取数据
        $conF[] = ['id', 'in', $firstIds];
        $listArr = self::where($conF)->column('id,bus_id,driver_id,kilometer,gps_mile,time,number,prize', 'bus_id');
        return $listArr;
    }

    /**
     * 每年行驶公里数
     */
    public static function yearlyMile($year) {
        if (!$year) {
            $year = date('Y');
        }

        $busCon[] = ['owner_type', '=', 'self'];
        $busList = BusService::where($busCon)->order('passenger_max desc')->field('id,current_driver')->select();
        // 提取公里数偏差数据
        $kiloListSql = self::where()
                ->field("bus_id,max( kilometer ) AS thisKilo,date_format( time, '%Y' ) AS year,date_format( time, '%m' ) AS `month`"
                        . ",date_format( time, '%Y-%m' ) AS yearmonth,time")
                ->group("bus_id,date_format( time, '%Y-%m' )")
                ->buildSql();
        $sql = "select a.*,b.thisKilo as lastKilo,(a.thisKilo - b.thisKilo) as kiloDiff "
                . "from " . $kiloListSql . " as a left join " . $kiloListSql . " as b on a.bus_id = b.bus_id and a.yearmonth = date_format( date_add( b.time, INTERVAL 1 MONTH ),'%Y-%m' )";
        $kiloListArr = Db::query($sql);
        // ->select();
        // $kiloListArr = $listArr ? $listArr->toArray() : [];
        $dataArr = [];
        foreach ($busList as $v) {
            $tmp = [];
            $tmp['bus_id'] = $v['id'];
            $tmp['current_driver'] = $v['current_driver'];
            $arr = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
            foreach ($arr as $vv) {
                $cone = [];
                $cone[] = ['bus_id', '=', $v['id']];
                $cone[] = ['year', '=', $year];
                $cone[] = ['month', '=', $vv];
                $info = Arrays2d::listFind($kiloListArr, $cone);
                $tmp[$vv] = Arrays::value($info, 'kiloDiff', 0);
            }
            $dataArr[] = $tmp;
        }

        return $dataArr;
    }

    /**
     * 钩子-删除前
     */
    public function extraPreDelete() {
        
    }

    /**
     * 钩子-删除后
     */
    public function extraAfterDelete() {
        
    }

    /**
     * 日加油上报数组：缓存查询
     * @param type $date
     * @return type
     */
    public static function dateOilingLogArr($date) {
        $cacheKey = __METHOD__ . $date;
        return Cachex::funcGet($cacheKey, function() use ($date) {
                    $con[] = ['time', '>=', date('Y-m-d 00:00:00', strtotime($date))];
                    $con[] = ['time', '<=', date('Y-m-d 23:59:59', strtotime($date))];
                    $lists = self::lists($con, 'time desc');
                    $listsArr = $lists ? $lists->toArray() : [];
                    return $listsArr;
                }, true, 60);
    }

    /**
     * 20220919 车辆日末次加油
     * @param type $busId
     * @param type $time
     * @return type
     */
    public static function busDailyLastOilingLog($busId, $time = '') {
        if (!$time) {
            $time = date('Y-m-d H:i:s');
        }
        $date = date('Y-m-d', strtotime($time));
        $todayWashArr = self::dateOilingLogArr($date);
        $con[] = ['bus_id', '=', $busId];
        $lastWash = Arrays2d::listFind($todayWashArr, $con);

        return $lastWash;
    }

    /**
     * 20221001：车辆提取末次加油记录id
     * @param type $busId
     */
    protected static function lastOilingId($busId, $time = '', $con = []) {
        $con[] = ['bus_id', '=', $busId];
        if ($time) {
            $con[] = ['time', '<', $time];
        }
        return self::where($con)->order('time desc')->cache(1)->value('id');
    }
    /**
     * 末次加满id
     * @createTime 2023-11-12
     * @param type $busId
     * @param type $time
     * @param type $con
     * @return type
     */
    protected static function lastFullId($busId, $time = '', $con = []) {
        $con[] = ['is_full', '=', 1];
        return self::lastOilingId($busId, $time, $con);
    }
    /**
     * 20221001：判断车辆近1小时是否有加油记录（防止重复上报）
     */
    public static function recentUplId($busId) {
        $con[] = ['bus_id', '=', $busId];
        $con[] = ['time', '<', date('Y-m-d H:i:s', strtotime('-1 hour'))];
        return self::where($con)->value('id');
    }

    /**
     * 计算上次加满的数据
     */
    public function calLastFullData() {
        $info = $this->get();
        if (!$info) {
            throw new Exception('查无加油记录' . $this->uuid . ',请联系开发');
        }
        $cone = [];
        $cone[] = ['time', '<', $info['time']];
        $cone[] = ['is_full', '=', 1];
        $cone[] = ['bus_id', '=', $info['bus_id']];
        $lastFullInfo = self::where($cone)->order('time desc')->find();
        // 自从上次加满后，累计的加油升数，金额

        $conSum = [];
        $conSum[] = ['time', '<=', $info['time']];
        $conSum[] = ['time', '>', $lastFullInfo['time']];
        $conSum[] = ['bus_id', '=', $info['bus_id']];
        $thisFullSum = $lastFullInfo ? self::where($conSum)->field('sum(prize) as prizeAll,sum(number) as numberAll')->find() : [];

        $data['last_full_kilometer'] = $info['is_full'] && $lastFullInfo ? Arrays::value($lastFullInfo, 'kilometer') : null;
        $data['last_full_gps_mile'] = $info['is_full'] && $lastFullInfo ? Arrays::value($lastFullInfo, 'gps_mile') : null;
        $data['accum_prize'] = $info['is_full'] && $lastFullInfo ? Arrays::value($thisFullSum, 'prizeAll', 0) : null;
        $data['accum_number'] = $info['is_full'] && $lastFullInfo ? Arrays::value($thisFullSum, 'numberAll', 0) : null;
        $data['update_time'] = date('Y-m-d H:i:s');
        return $data;
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
                            ->group("company_id,bus_id,date_format( `time`, '" . $groupField . "' ) " . $orderByStr)
                            ->field("company_id,bus_id,
                                    date_format( `time`, '" . $groupField . "' ) as belongTime,
                                    count(*) as oilingCount")
                            ->select();
                    return $data ? $data->toArray() : [];
                }, $orderBy);
    }

    /**
     * 添加公里数
     * @return type
     * @throws Exception
     */
    public function mileSync(){
        $info = $this->get();
        
        $time   = Arrays::value($info, 'time');
        $mile   = Arrays::value($info, 'kilometer');
        if(!$mile){
            return false;
        }

        $busId          = Arrays::value($info, 'bus_id');
        $fromTable      = self::getTable();
        $fromTableId    = $this->uuid;
        
        $data                   = [];
        $data['upl_user_id']    = Arrays::value($info, 'driver_id');
        $mileId = BusMileService::mileSyncGetId($busId, $mile, $time, $fromTable, $fromTableId, $data);
        // 20231205:更新关联字段
        $updData = ['mile_id'=>$mileId];
        return $this->doUpdateRam($updData);
    }
    
        
    /**
     * 20231216:订单账单添加
     */
    public function addStatementOrder() {
        $info = $this->get();
        // 20231228
        if(!$info['driver_id']){
            return false;
        }
        // 20231231:加油只处理现金
        if($info['pay_by'] != 'cash'){
            return false;
        }
        
        $prizeKey   = $info['pay_by'] == 'cash' ? 'staffFee' : 'customerBill';
        $prizeField = 'prize';

        $reflectKeys = ['driver_id'=>'user_id'];

        return $this->financeCommAddStatementOrder($prizeKey, $prizeField, $reflectKeys);
    }
    
    public function updateStatementOrder() {
        // 如果款项信息有变
        // 删除之前的账单
        // 再新增账单
        if($this->updateDiffsHasField(['driver_id','prize','pay_by'])){
            // 先清理
            $this->financeCommClearStatementOrder();
            // 再添加
            $this->addStatementOrder();
        }
    }
    
    
}
