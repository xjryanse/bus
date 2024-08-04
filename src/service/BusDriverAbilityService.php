<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\bus\service\BusTypeService;
use xjryanse\bus\service\BusService;
use app\view\service\ViewDriverService;
use xjryanse\user\service\UserService;
use xjryanse\logic\Arrays;
use xjryanse\logic\Debug;
use xjryanse\logic\DbOperate;
use xjryanse\sql\service\SqlService;
use think\Db;
use Exception;

/**
 * 车辆驾驶员能力
 */
class BusDriverAbilityService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\ObjectAttrTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusDriverAbility';
    //直接执行后续触发动作
    protected static $directAfter = true;

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use ($ids) {
                    return $lists;
                }, true);
    }

    /**
     * 传入需求车型；空闲司机；空闲车辆；得到一个排班
     * 司机优先；营业额从小到大
     * 车辆优先；匹配车型最优
     * 当只有一种匹配车型；选择匹配的车辆；司机；
     * 当有多车型；
     * 
     * 
     */
    public static function getArrangeBusDriver($busTypeId, $idleDriverIds, $idleBusIds, $driverMoneys) {
        $busTypeInfo = BusTypeService::getInstance($busTypeId)->get();
        $persons = Arrays::value($busTypeInfo, 'passenger_max', 0);

        $busTable = BusService::getTable();
        $userTable = UserService::getTable();
        //空闲司机条件
        $con[] = ['a.driver_id', 'in', $idleDriverIds];
        //空闲车条件
        $con[] = ['a.bus_id', 'in', $idleBusIds];
        //座位数限制条件
        $con[] = ['b.passenger_max', '>=', $persons];
        $con[] = ['b.passenger_max', '<=', $persons + 10];
        if (!$busTypeInfo['is_replace']) {
            $con[] = ['b.bus_type', '=', $busTypeId];
        }
        Debug::debug('查询条件', $con);
        $dataArr = self::mainModel()->alias('a')
                ->where($con)
                ->join($busTable . ' b', 'a.bus_id=b.id')
                ->join($userTable . ' c', 'a.driver_id = c.id')
                ->field('a.bus_id,a.driver_id,c.realname,a.priority,rely_rate,b.licence_plate,b.bus_type,b.passenger_max')
                ->order('b.passenger_max,a.priority')
                ->select();
        $data = $dataArr ? $dataArr->toArray() : [];
        Debug::debug('查询sql', self::mainModel()->getLastSql());
        Debug::debug('$data', $data);

        //驾驶员营业额偏差系数(>1）
        $moneyRate = self::getMoneyRate($driverMoneys);
        foreach ($data as &$v) {
            // 车辆车型匹配系数
            $seatRate = self::seatRate($persons, $v['passenger_max']);
            // 司机营业额偏差
            $driverMoneyRate = $moneyRate[$v['bus_id']] ?: 1;
            // 车辆对驾驶员依赖度系数
            $relyRate = $v['rely_rate'];
            //最终综合系数
            $rateAll = $seatRate * $driverMoneyRate * $relyRate;
            $v['$seatRate'] = $seatRate;
            $v['$driverMoneyRate'] = $driverMoneyRate;
            $v['$relyRate'] = $relyRate;

            $v['rateAll'] = $rateAll;
        }
        // 
        $sortFieldArr = array_column($data, 'rateAll');
        array_multisort($sortFieldArr, SORT_DESC, $data);

        return $data ? $data[0] : [];
    }

    /**
     * 各司机的营业额系数
     * 保证各位司机，营业额偏差不会过大
     * 
     */
    public static function getMoneyRate($driverMoneys = []) {
        if (!$driverMoneys) {
            throw new Exception('$driverMoneys参数必须');
        }
        //设定基准系数为1；
        $baseRate = 1;
        //最大营业额-当前营业额
        $rateArr = [];
        $max = max($driverMoneys);
        //营业额平均值
        $average = intval(array_sum($driverMoneys) / count($driverMoneys));
        foreach ($driverMoneys as $driverId => $money) {
            //（最大值-当前值）/（最大值-最小值）
            $extraRate = intval(($max - $money) * 100 / $average) / 100;
            //基准系数+偏差系数；
            $rateArr[$driverId] = $baseRate + $extraRate;
        }

        return $rateArr;
    }

    /**
     * 上座率越大越好
     * @param type $needSeat        需求座位数
     * @param type $actualSeat      实际车座位数
     */
    public static function seatRate($needSeat, $actualSeat) {
        //座位偏小，完全不匹配
        if ($actualSeat < $needSeat) {
            return 0;
        }
        $rate = intval($needSeat / $actualSeat * 100) / 100;
        return $rate;
    }

    /**
     * 钩子-保存前
     */
    public static function extraPreSave(&$data, $uuid) {
        
    }

    /**
     * 钩子-保存后
     */
    public static function extraAfterSave(&$data, $uuid) {
        $info = self::getInstance($uuid)->get();
        self::updateRelyRate($info['bus_id']);
    }

    /**
     * 钩子-更新前
     */
    public static function extraPreUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-更新后
     */
    public static function extraAfterUpdate(&$data, $uuid) {
        $info = self::getInstance($uuid)->get();
        self::updateRelyRate($info['bus_id']);
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

    /*
     * 更新依赖率
     */

    public static function updateRelyRate($busId) {
        $con[] = ['bus_id', '=', $busId];
        $listsRaw = self::mainModel()->where($con)->field('id,priority')->select();
        $lists = $listsRaw ? $listsRaw->toArray() : [];
        $sum = array_sum(array_column($lists, 'priority'));
        foreach ($lists as &$v) {
            $v['rely_rate'] = $sum ? intval($v['priority'] / $sum * 10000) / 100 : 0;
        }
        self::mainModel()->saveAll($lists);
    }

    /**
     * 重置驾驶员能力数据
     */
    public static function abilityReset($companyId, $dataArr = []) {
        //用来计算车辆和驾驶员的依赖率
        $busIds = array_unique(array_column($dataArr, 'bus_id'));
        $busSumArr = [];
        foreach ($busIds as $busId) {
            $arr = array_filter($dataArr, function($element) use ($busId) {
                if ($element['bus_id'] == $busId) {
                    return $element;
                }
            });
            $busSumArr[$busId] = array_sum(array_column($arr, 'priority'));
        }
        self::checkTransaction();
        $con[] = ['company_id', '=', $companyId];
        $licencePlates = BusService::mainModel()->where($con)->column('id', 'licence_plate');
        $drivers = ViewDriverService::mainModel()->where($con)->column('id', 'realname');
        $tmpArr = [];
        foreach ($dataArr as &$v) {
            $tmp = [];
            $tmp['bus_id'] = Arrays::value($licencePlates, $v['licence_plate'], $v['licence_plate']);
            $tmp['driver_id'] = Arrays::value($drivers, $v['realname'], $v['realname']);
            $tmp['priority'] = $v['priority'];
            $tmp['rely_rate'] = $busSumArr[$v['bus_id']] ? (intval($v['priority'] / $busSumArr[$v['bus_id']] * 10000) / 100) * 100 : 0;
            $tmpArr[] = $tmp;
        }
        //删原有
        self::mainModel()->where($con)->delete();
        //添新的
        self::saveAll($tmpArr);
        //更新车辆依赖率
        $buses = array_unique(array_column($tmpArr, 'bus_id'));
        foreach ($buses as $busId) {
            self::updateRelyRate($busId);
        }

        return $dataArr;
    }

    /**
     * 驾驶员能力匹配算法：
     * 1、如果是车辆和司机绑定的，基准能力+50
     * 2、提取近3个月的排班情况，出车一天 + 1
     */
    public static function recentAbilityStatics() {
        $startDate = date('Y-m-d H:i:s', strtotime('-3 month'));

        $con[] = ['b.start_time', '>=', $startDate];
        $con[] = ['b.start_time', '<=', date('Y-m-d H:i:s')];
        $res = Db::name('order_bao_bus_driver')->alias('a')->join('w_order_bao_bus b', 'a.bao_bus_id = b.id')
                ->where($con)
                ->field('bus_id,driver_id,count(1) as number')
                ->group('bus_id,driver_id')
                ->select();
        dump($res);
    }
    
    /**
     * 车辆的司机列表
     * @return type
     */
    public static function busDriverArrList(){
//        $arr    = [];
//        $arr[]  = ['table_name'=>'w_bus_driver_ability','alias'=>'tA'];
//        $arr[]  = ['table_name'=>'w_user','alias'=>'tB','join_type'=>'inner','on'=>'tA.driver_id=tB.id'];
//
//        $fields     = [];
//        $fields[]   = 'tA.bus_id'; 
//        $fields[]   = 'group_concat(tB.realname) AS driverName';
//        $groupFields    = ['tA.bus_id'];
//        $sql            = DbOperate::generateJoinSql($fields,$arr,$groupFields);

        $sql  =  SqlService::keyBaseSql('busAbilityDrivers');
        // dump($sql);
        $roleArrList    = Db::query($sql);
        return $roleArrList;
    }
    /**
     * 车辆id，提取驾驶员id
     */
    public static function busDriverIds($busId){
        $con[] = ['bus_id','in',$busId];
        return self::where($con)->column('distinct driver_id');
    }
    /**
     * 驾驶员id，提取车辆id
     */
    public static function driverBusIds($driverId){
        $con[] = ['driver_id','in',$driverId];
        return self::where($con)->column('distinct bus_id');
    }

    public static function busDriverArr($driverId){
        
    }
    
}
