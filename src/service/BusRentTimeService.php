<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Datetime;
use xjryanse\logic\Arrays2d;
use xjryanse\logic\Arrays;
use xjryanse\logic\DataCheck;
use xjryanse\logic\Cachex;
use xjryanse\logic\DbOperate;
use think\Db;
use Exception;
/**
 * 车辆持股表
 */
class BusRentTimeService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticsModelTrait;    

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusRentTime';

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use ($ids) {
            foreach($lists as &$v){
                $v['minutes'] = Datetime::minuteDiff($v['end_time'], $v['start_time'],false);
                $v['hours']   = Datetime::hourDiff($v['end_time'], $v['start_time'],false);
                $v['days']    = Datetime::dayDiff($v['end_time'], $v['start_time'],false);
            }

            return $lists;
        }, false);
    }
    /**
     * 20230723:根据开始时间，结束时间，计算
     * @param type $data
     */
    protected static function calTime($data){
        $endTime    = Arrays::value($data, 'end_time');
        $startTime  = Arrays::value($data, 'start_time');

        $data['minutes'] = $startTime && $endTime ? Datetime::minuteDiff($endTime, $startTime,false) : 0;
        $data['hours']   = $startTime && $endTime ? Datetime::hourDiff($data['end_time'], $data['start_time'],false) : 0;
        $data['days']    = $startTime && $endTime ? Datetime::dayDiff($data['end_time'], $data['start_time'],false) : 0;
        return $data;
    }

    public static function extraPreSave(&$data, $uuid) {
        $notice['bus_id']       = '车辆必须';
        $notice['start_time']   = '开始用车时间必须';
        DataCheck::must($data, array_keys($notice), $notice);
        $data = self::calTime($data);
    }
    
    /**
     * 钩子-更新前
     */
    public static function extraPreUpdate(&$data, $uuid) {
        $info       = self::getInstance($uuid)->get();
        $infoArr    = $info ? $info->toArray() : [];
        $dataArr    = array_merge($infoArr,$data);
        $data       = self::calTime($dataArr);
    }
    
    /**
     * 20230722:查询车辆列表（带状态）
     * @param type $param
     * @return type
     */
    public static function buses($param){
        $cate = Arrays::value($param, 'cate');
        $con = [];
        // 租用中
        if($cate == 'renting'){
            $rentingBusIds = self::rentingBusIds();
            $con[] = ['id','in',$rentingBusIds];
        }
        // 空闲中
        if($cate == 'empty'){
            $rentingBusIds = self::rentingBusIds();
            $con[] = ['id','not in',$rentingBusIds];
        }
        // 全部
        return self::busesAll($con);
    }
    
    /**
     * 20230722:全部车辆
     */
    protected static function busesAll($con = []){
        $buses      = BusService::lists($con);
        $busesArr   = $buses ? $buses->toArray() : [];
        return self::busesAddRentParam($busesArr);
    }

    /**
     * 20230723：车辆添加租赁参数
     */
    protected static function busesAddRentParam(&$busesArr){
        $rentingBusIds = self::rentingBusIds();
        // 今日租用时长
        $con[] = ['start_time','>=',date('Y-m-d 00:00:00')];
        $con[] = ['start_time','<=',date('Y-m-d 23:59:59')];
        $todayHours = self::busStaticsArr($con);
        $hoursArr = Arrays2d::fieldSetKey($todayHours, 'bus_id');
        // 租赁中记录
        $arr        = self::rentingRecords();        
        $busRentObj = Arrays2d::fieldSetKey($arr, 'bus_id');

        foreach($busesArr as &$v){
            // 出租状态：0空闲中；1租赁中
            $v['busRentState']      = in_array($v['id'],$rentingBusIds) ? 1 : 0;
            $dataArr                = Arrays::value($hoursArr, $v['id'], []);
            // 今日已租用几小时
            $v['todayRentHours']    = Arrays::value($dataArr, 'hours', 0);
            $startTime              = Arrays::value($busRentObj, $v['id']) ? $busRentObj[$v['id']]['start_time'] : '';
            $v['thisRentHours']     = $startTime ? Datetime::hourDiff(date('Y-m-d H:i:s'), $startTime,false) : 0;
        }

        $keys = ['id','licencePlateSeats','busRentState','todayRentHours','thisRentHours'];
        return Arrays2d::getByKeys($busesArr, $keys);
    }
    /**
     * 车辆的末次租赁记录
     */
    public static function busLastRecord($busId){
        $con[] = ['bus_id','=',$busId];
        $info = self::where($con)->order('start_time desc')->find();
        return $info;
    }

    /**
     * 20230722:点击开始租用
     * @param type $busId
     */
    public static function busRentStart($busId, $param = []){
        $lastInfo = self::busLastRecord($busId);
        if($lastInfo['start_time'] && !$lastInfo['end_time']){
            throw new Exception('上次租赁记录未完结'.$lastInfo['start_time']);
        }
        
        $data['bus_id']     = $busId;
        $data['start_time'] = Arrays::value($param, 'start_time') ? : date('Y-m-d H:i:s');
        return self::save($data);
    }
    
    /**
     * 20230722:点击结束租用
     * @param type $busId
     */
    public static function busRentEnd($busId, $param = []){
        $lastInfo = self::busLastRecord($busId);
        if(!$lastInfo){
            throw new Exception('末次租赁记录不存在');
        }
        if($lastInfo['end_time']){
            throw new Exception('末次租赁记录已完结'.$lastInfo['end_time']);
        }
        $endTime = Arrays::value($param, 'end_time') ? : date('Y-m-d H:i:s');
        if($endTime < $lastInfo['start_time']){
            throw new Exception('结束时间不可小于开始时间'.$lastInfo['start_time']);
        }
        return self::getInstance($lastInfo['id'])->update(['end_time'=>$endTime]);
    }
    /**
     * 车辆租用情况获取
     */
    public static function busRentGet($param){
        $busId = Arrays::value($param, 'bus_id');
        $info['id']                 = $busId;
        $arrRaw = [$info];
        $arr = self::busesAddRentParam($arrRaw);
        return $arr[0];
    }
    /**
     * 20230723：车辆统计分页列表
     */
    public static function busStaticsPgList($param){
        $type = Arrays::value($param, 'type');
        $con = [];
        if($type == 'today'){
            $con[] = ['start_time','>=',date('Y-m-d 00:00:00')];
            $con[] = ['start_time','<=',date('Y-m-d 23:59:59')];
        }
        if($type == 'yesterday'){
            $con[] = ['start_time','>=',date('Y-m-d 00:00:00',strtotime('-1 day'))];
            $con[] = ['start_time','<=',date('Y-m-d 23:59:59',strtotime('-1 day'))];
        }
        if($type == 'seven'){
            $con[] = ['start_time','>=',date('Y-m-d 00:00:00',strtotime('-7 day'))];
            $con[] = ['start_time','<=',date('Y-m-d 23:59:59')];
        }
        if($type == 'thirty'){
            $con[] = ['start_time','>=',date('Y-m-d 00:00:00',strtotime('-30 day'))];
            $con[] = ['start_time','<=',date('Y-m-d 23:59:59')];
        }
        
        $res = self::busStaticsArr($con);
        
        
        $conf[] = ['id', 'not in', array_column($res,'bus_id')];
        $conf[] = ['status', '=', 1];
        // 拼接未出车的数据
        $noBusIds = BusService::where($conf)->order('passenger_max desc')->column('id');
        foreach($noBusIds as $noBusId){
            $tmpData = [
                'bus_id'    =>$noBusId,
                'num'       => 0,
                'minutes'   => 0,
                'hours'     => 0,
                'days'      => 0,
            ];
            $res[] = $tmpData;
        }
        
        return $res;
    }
    
    /**
     * 20230723:车统计数据
     * @param type $con
     */
    public static function busStaticsArr($con = []){
        $sumFields = ['minutes','hours','days'];
        return self::staticsRanking('bus_id', $sumFields, 'hours desc', $con);
    }
    
    /**
     * 20230723:租赁中记录
     */
    public static function rentingRecords(){
        return Cachex::funcGet(__METHOD__, function(){
            // 有开始时间
            $con[] = ['hasStartTime','=',1];
            // 没有结束时间
            $con[] = ['hasEndTime','=',0];

            $res = self::where($con)->select();
            return $res ? $res->toArray() : [];
        },true,1);
    }
    /**
     * 20230723：租赁中车辆id
     */
    public static function rentingBusIds(){
        $arr = self::rentingRecords();
        return array_unique(array_column($arr, 'bus_id'));
    }
    
    
    /**
     * 20240105:指定一个时间点，提取目标列表
     */
    public static function targetTimeCon($time = ''){
        if(!$time){
            $time = date('Y-m-d H:i:s');
        }

        $startTimeKey   = 'tA.start_time';
        $endTimeKey     = 'tA.end_time';
        // 开始时间大于给定时间 且 结束时间小于给定时间

        $con    = [];
        $con[]  = [$startTimeKey, '>=', $time];
        $con[]  = [$endTimeKey, '<=', $time];
        return $con;
    }
    
    /**
     * 车辆的司机列表
     * @return type
     */
    public static function targetTimeList(){
        $arr    = [];
        $arr[]  = ['table_name'=>'w_bus_rent_time','alias'=>'tA'];
        $arr[]  = ['table_name'=>'w_customer','alias'=>'tB','join_type'=>'inner','on'=>'tA.customer_id=tB.id'];

        $fields     = [];
        $fields[]   = 'tA.bus_id'; 
        $fields[]   = 'group_concat(tB.customer_name) AS customerName';
        $groupFields    = ['tA.bus_id'];
        
        $con            = self::targetTimeCon();
        
        $sql            = DbOperate::generateJoinSql($fields,$arr,$groupFields,$con);
        $roleArrList    = Db::query($sql);
        return $roleArrList;
    }
    
    
    
}
