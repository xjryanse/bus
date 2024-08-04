<?php
namespace xjryanse\bus\service\oiling;

use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;

/**
 * 
 */
trait StaticsTraits{
    /**
     * 20231225:用于数据条显示统计结果
     */
    public static function staticsDataBarBus($param){
        $yearmonth = Arrays::value($param, 'yearmonth') ? : date('Y-m');
        //TODO封装？？
        $startTime = date('Y-m-01 00:00:00', strtotime($yearmonth));
        $endTime = date('Y-m-d 23:59:59', strtotime($yearmonth . " +1 month -1 day"));

        $data = self::scopeStatics($startTime, $endTime);
        
        // 提取首次id，用于剔除
        $firstIds   = self::busFirstOilIds();
        $busId      = Arrays::value($param, 'bus_id');

        $con[]      = ['bus_id', 'in', $busId];
        $con[]      = ['id', 'not in', $firstIds];
        $listsArr = self::timeScopeArr($startTime, $endTime, $con);
        //todo：更科学？
        $mileBusArr = self::busMileDiffArr($startTime, $endTime);
        $mileBusObj = Arrays2d::fieldSetKey($mileBusArr, 'id');
        $bus = $mileBusObj[$busId];
        // 用于比较的加油数组
        $cone = [];
        $cone[] = ['bus_id', '=', $busId];
        // 有效加油次数
        $bus['effOilCounts'] = count(Arrays2d::listFilter($listsArr, $cone));
        // 加油升数（需剔除第一条记录）
        $bus['oilVolume'] = round(array_sum(array_column(Arrays2d::listFilter($listsArr, $cone), 'number')), 2);
        // 加油金额（需剔除第一条记录）
        $bus['oilPrize'] = round(array_sum(array_column(Arrays2d::listFilter($listsArr, $cone), 'prize')), 2);
        
        
        // 公里偏差值
        $mileDiff = $bus['mileDiff'];
        //每公里油耗
        $bus['perKilometerVolume']  = $mileDiff ? round($bus['oilVolume'] / $mileDiff, 3) : 0;
        //每公里油钱
        $bus['perKilometerPrize']   = $mileDiff ? round($bus['oilPrize'] / $mileDiff, 3) : 0;
        // 2022-11-15 百公里油耗
        $bus['100KmVolume']         = round($bus['perKilometerVolume'] * 100, 1);
        // 2022-11-15 百公里油钱
        $bus['100KmPrize']          = round($bus['perKilometerPrize'] * 100, 1);

        return $bus;
    }

}
