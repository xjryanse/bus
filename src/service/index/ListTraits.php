<?php
namespace xjryanse\bus\service\index;

use xjryanse\bus\service\BusOilingService;
use xjryanse\logic\Datetime;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;
use xjryanse\logic\Number;
use xjryanse\logic\ModelQueryCon;
use xjryanse\statics\service\StaticsTimeService;
use xjryanse\bus\service\BusDriverAbilityService;
use xjryanse\bus\service\BusRentTimeService;
use app\order\service\OrderBaoBusService;
use xjryanse\sql\service\SqlService;
use think\Db;
/**
 * 触发复用
 */
trait ListTraits{

    /**
     * 
     * @param type $busCon  车辆条件
     * @param type $timeScope 时间范围：[开始时间,结束时间]
     * @param type $staticsKey  费用key
     * @return type
     */
    protected static function listStatics($busCon = [],$timeScope=[], $staticsKey = 'busFeeStatics') {
        // 20231203:出车
        $busDrivers = OrderBaoBusService::calScopeTimeBaoBusDriverStr($timeScope);
        $busDriverArr = array_column($busDrivers, 'driverNames','bus_id');
        
        $lists = self::where($busCon)
                ->field('id,dept_id,passenger_max,licencePlateSeats,current_driver,status')
                ->order('owner_type desc,passenger_max desc')->select();
        $arr    = $lists ? $lists->toArray() : [];
        // $busIds = array_column($arr, 'id');
        $arrKey     = 'id';
        // $feeArr     = StaticsTimeService::calGroupStaticsByKey($staticsKey, $busIds, $timeScope);
        $arrN = StaticsTimeService::addStaticsDataArr($arr, $staticsKey,  $arrKey, $timeScope);
        foreach($arrN as &$v){
            // 指定时段内有出车的司机
            $v['outDriverStr'] = Arrays::value($busDriverArr, $v['id']);
            // 加油：报销：高速等费用的聚合组合
            // 收入
            $inPrize            = $v['stInPrize'];
            // 支出
            $outPrize           = $v['stOutPrize'];
            // 毛利
            $v['finalPrize']    = Number::minus($inPrize, $outPrize);
            // 用来排序比较方便
            $v['dSort']         = $v['hasData'] * 100 + $v['passenger_max'];
            // 最终抽成率
            $v['finalRate']     = Number::rate($v['finalPrize'], $inPrize);
        }

        $arrT = Arrays2d::sort($arrN, 'dSort', 'desc');
        // 没有出车趟的项目，删除
        foreach($arrT as $k1 =>$v1){
            if(!$v1['status'] && !$v1['hasData']){
                unset($arrT[$k1]);
            }
        }

        return $arrT;
    }
    /**
     * 自有车 - 日统计
     * @param type $param
     */
    public static function listSelfDailyStatics($param){
        
        $fields['equal'] = ['dept_id'];
        $busCon = ModelQueryCon::queryCon($param, $fields);
        // 车辆条件
        $busCon[] = ['owner_type','=','self'];
        // 时间条件
        $date   = Arrays::value($param, 'date') ? : date('Y-m-d');
        $timeScope = Datetime::dateScopeTimes($date);
        
        $staticsKey = 'busFeeStaticsDaily';
        $lists = self::listStatics($busCon,$timeScope, $staticsKey);
        
        foreach($lists as &$v){
            $v['date'] = $date;
        }
        
        return $lists;
    }
    /**
     * 自有车 - 月统计
     */
    public static function listSelfMonthlyStatics($param, $busCon= []){
        // 车辆条件
        $fields['equal'] = ['dept_id'];
        $busCon = array_merge($busCon,ModelQueryCon::queryCon($param, $fields));
        // 车辆条件
        $busCon[] = ['owner_type','=','self'];

        // 时间条件
        $yearmonth   = Arrays::value($param, 'yearmonth') ? : date('Y-m');
        $timeScope = Datetime::monthlyScopeTimes($yearmonth);

        $staticsKey = 'busFeeStatics';
        $lists      = self::listStatics($busCon,$timeScope, $staticsKey);
        // 20231207油耗
        $oilData    = BusOilingService::scopeStatics($timeScope[0], $timeScope[1]);
        $oilObj     = Arrays2d::fieldSetKey($oilData, 'bus_id');

        foreach($lists as &$v){
            $v['yearmonth'] = $yearmonth;
            // $v['$data'] = $oilObj;
            //根据加油统计出来的里程数
            $v['mileDiff']    = isset($oilObj[$v['id']])
                    ? Arrays::value($oilObj[$v['id']], 'mileDiff')
                    : '';
            //百公里油耗
            $v['100KmVolume']   = isset($oilObj[$v['id']])
                    ? Arrays::value($oilObj[$v['id']], '100KmVolume')
                    : '';
            //百公里油钱
            $v['100KmPrize']    = isset($oilObj[$v['id']])
                    ? Arrays::value($oilObj[$v['id']], '100KmPrize')
                    : '';
        }

        return $lists;
    }
    
    /**
     * 自有车 - 年统计
     */
    public static function listSelfYearlyStatics($param, $busCon= []){
        // 车辆条件
        $fields['equal'] = ['dept_id'];
        $busCon = array_merge($busCon,ModelQueryCon::queryCon($param, $fields));
        // 车辆条件
        $busCon[] = ['owner_type','=','self'];

        // 时间条件
        $year      = Arrays::value($param, 'year') ? : date('Y');
        $timeScope = Datetime::monthlyScopeTimes($year);

        $staticsKey = 'busFeeStatics';
        $lists      = self::listStatics($busCon,$timeScope, $staticsKey);
        // 20231207油耗
        $oilData    = BusOilingService::scopeStatics($timeScope[0], $timeScope[1]);
        $oilObj     = Arrays2d::fieldSetKey($oilData, 'bus_id');

        foreach($lists as &$v){
            $v['year']          = $year;
            // $v['$data'] = $oilObj;
            //根据加油统计出来的里程数
            $v['mileDiff']      = isset($oilObj[$v['id']])
                    ? Arrays::value($oilObj[$v['id']], 'mileDiff')
                    : '';
            //百公里油耗
            $v['100KmVolume']   = isset($oilObj[$v['id']])
                    ? Arrays::value($oilObj[$v['id']], '100KmVolume')
                    : '';
            //百公里油钱
            $v['100KmPrize']    = isset($oilObj[$v['id']])
                    ? Arrays::value($oilObj[$v['id']], '100KmPrize')
                    : '';
        }

        return $lists;
    }
    
    /**
     * 自有车 - 年统计
     */
    public static function listSelfYearlyStaticsGroupMonth($param){
        // 车辆条件
        // $busCon[]   = ['owner_type','=','self'];
        $busCon[]   = ['id','in',$param['bus_id']];
        // todo:参考业务员下钻比较科学
        $year       = Arrays::value($param, 'year') ? : '2024';
        $monthes    = Datetime::yearlyMonthes($year);
        $arr        = [];
        foreach($monthes as $yearmonth){
            $param['yearmonth'] = $yearmonth;
            // 提取每个月份
            $tmpArr = self::listSelfMonthlyStatics($param, $busCon);
            $arr    = array_merge($arr, $tmpArr);
        }
        
        return $arr;        
    }
    /**
     * 自有车当前状态
     */
    public static function listSelfCurrentState(){
        $busCon     = [];
        $busCon[]   = ['owner_type','=','self'];
        $busCon[]   = ['status','=',1];
        // 查今日
        $timeScope = Datetime::dateScopeTimes(date('Y-m-d'));
        $busDrivers = OrderBaoBusService::calScopeTimeBaoBusDriverStr($timeScope);
        $busDriverArr = array_column($busDrivers, 'driverNames','bus_id');
        
        $busList = self::where($busCon)->order('seats desc')->select();
        
        
        foreach($busList as &$v){
            // 20231207:今日出车司机
            $v['todayDriverStr'] = Arrays::value($busDriverArr, $v['id']);
        }
        
        return $busList ? $busList->toArray() : [];
    }
    /**
     * 20231229：列表带线路牌
     * 20240521:拟废弃
     * 使用以下接口替代
     * /admin/SSql/paginate?sqlKey=busBaseInfoFull
     */
    public static function listWithCircuitPlate($param){
        $fields             = [];
        $fields['like']     = ['licence_plate'];
        $fields['equal']    = ['dept_id','circuit_type','owner_type','status','circuit_home_id'];
        $con     = ModelQueryCon::queryCon($param, $fields);

        // $sqlLine = self::mainModel()->sqlBusWithCircuitPlate();
        $sqlLine = SqlService::keyBaseSql('busWithCircuitPlate');
        // dump($sqlLine);exit;
        $sqlCert = self::mainModel()->busCertSql();
        // dump($sqlCert);exit;
        
        $sqlTable = '(select * from '.$sqlLine.' as aae left join '.$sqlCert.' as bbe on aae.id = bbe.bus_id) as mainTable';

        $arr = Db::table($sqlTable)->where($con)->order('status desc,sort')->select();

        // 20240105:提取当前已绑司机
        $tmp            = BusDriverAbilityService::busDriverArrList();
        $driverNamesArr = Arrays2d::fieldSetKey($tmp, 'bus_id');
        // 20240105:提取当前租用记录
        $rentList   = BusRentTimeService::targetTimeList();
        $rentArr    = Arrays2d::fieldSetKey($rentList, 'bus_id');

        foreach($arr as &$v){
            $busDriverInfo              = Arrays::value($driverNamesArr, $v['id']);
            $v['driverNames']           = Arrays::value($busDriverInfo, 'driverName');
            // 当前租用客户信息：针对租用车辆
            $rentInfo                   = Arrays::value($rentArr, $v['id']);
            $v['rentCustomerName']      = Arrays::value($rentInfo, 'customerName');
        }
        
        return $arr;
    }
    
    
    /**
     * 20240104:只有线路牌的列表
     */
    public static function listCircuitPlate(){
        // 线路牌sql:TODO
        $sql="(SELECT
                a.* 
        FROM
                w_circuit_plate AS a
                INNER JOIN w_bus AS b ON a.bus_id = b.id 
        WHERE
                b.status=1 and b.discard_time is null and 
                b.busi_type IN ( SELECT DISTINCT busi_type FROM w_bus_busi_cert_key WHERE cert_key = 'line' )) as MainTable";
        
        $lists = Db::table($sql)->order('end_time')->cache(5)->select();
        
        foreach($lists as &$v){
            if(strtotime($v['end_time']) - time() > 86400 * 30 ){
                // 到期30日以上，正常
                $v['certStatus'] = 1;
            } else if(time() > strtotime($v['end_time'])){
                // 已过期
                $v['certStatus'] = 3;
            } else {
                // 即将到期
                $v['certStatus'] = 2;
            }
        }
        
        return $lists;
    }
    
    /**
     * 20240104:只有线路牌的列表
     */
    public static function listCert(){
        // 线路牌sql:TODO
        $sql="(SELECT
	a.*,b.id as bus_id,b.dept_id
FROM
	w_cert AS a
	INNER JOIN w_bus AS b ON a.belong_table_id = b.id
	INNER JOIN w_bus_busi_cert_key AS c ON b.busi_type = c.busi_type and c.cert_key = a.cert_key
WHERE
	c.cert_type = 'bus' and b.owner_type = 'self' and b.status=1 and a.time_manage=1 and b.discard_time is null) as MainTable";
        
        $lists = Db::table($sql)->order('cert_limit_time')->cache(5)->select();
        
        foreach($lists as &$v){
            if(strtotime($v['cert_limit_time']) - time() > 86400 * 30 ){
                // 到期30日以上，正常
                $v['certStatus'] = 1;
            } else if(time() > strtotime($v['cert_limit_time'])){
                // 已过期
                $v['certStatus'] = 3;
            } else {
                // 即将到期
                $v['certStatus'] = 2;
            }
        }
        
        return $lists;
    }

}
