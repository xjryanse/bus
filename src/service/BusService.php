<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\system\service\SystemCateService;
use app\card\service\CardService;
use app\cert\service\CertService;
use app\order\service\OrderBaoBusService;
use xjryanse\bus\service\BusDriverAbilityService;
use xjryanse\bus\service\BusTimelyFeeService;
use xjryanse\logic\Url;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;
use xjryanse\logic\Cachex;
use xjryanse\prize\service\PrizeRuleService;
use Exception;

/**
 * 
 */
class BusService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\ObjectAttrTrait;
    use \xjryanse\traits\StaticModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\Bus';
    //直接执行后续触发动作
    protected static $directAfter = true;
    
    use \xjryanse\bus\service\index\DoTraits;
    use \xjryanse\bus\service\index\CalTraits;
    use \xjryanse\bus\service\index\FieldTraits;
    use \xjryanse\bus\service\index\TriggerTraits;
    use \xjryanse\bus\service\index\ListTraits;
    use \xjryanse\bus\service\index\PaginateTraits;

    /**
     * 20220511
     * 部门人员只看自己部门，其他人(高管)可看全部
     * @return type
     */
    public static function extraDataAuthCond() {
        return [];

    }

    /**
     * 车辆id，提取客户id数组
     */
    public static function busCustIdArr($busIds) {
        $con[] = ['id', 'in', $busIds];
        $lists = self::staticConList($con);
        $arr = array_column($lists, 'customer_id', 'id');
        return $arr;
    }

    /**
     * id数组
     * @param type $licencePlates
     */
    public static function licencePlateGetIds($licencePlates) {
        $con[] = ['licence_plate', 'in', $licencePlates];
        $con[] = ['company_id', '=', session(SESSION_COMPANY_ID)];
        return self::mainModel()->where($con)->column('id');
    }
    
    
    /**
     * 员工姓名转id，仅支持当前员工
     */
    public static function licencePlateToId($licencePlate){
        $con[] = ['licence_plate','=',$licencePlate];
        // $con[] = ['status','=',1];
        
        $res = self::staticConList($con);
        
        if(count($res) > 1){
            throw new Exception($licencePlate.'有多个,无法匹配');
        }

        return $res ? $res[0]['id'] : '' ;
    }
    

    /**
     * 初始化车辆证件数据
     */
    public static function busCertInit($busId) {
        //key为车辆证件key
        $certKeys = SystemCateService::columnByGroup('dBusCert');
        $keys = array_keys($certKeys);
        $dataArr = [];
        foreach ($keys as $key) {
            $tmpData = [];
            $tmpData['belong_table'] = self::mainModel()->getTable();
            $tmpData['belong_table_id'] = $busId;
            $tmpData['cert_key'] = $key;
            $dataArr[] = $tmpData;
        }
        return CertService::saveAll($dataArr);
    }

    /**
     * 20220527根据设备号取id
     */
    public static function getIdByEquipment($equipment) {
        $key = __CLASS__ . __FUNCTION__ . $equipment;
        return Cachex::funcGet($key, function() use ($equipment) {
                    $con[] = ['equipment', '=', $equipment];
                    return self::mainModel()->where($con)->value('id');
                });
    }

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use ($ids) {
                    // $cond[] = ['bus_id', 'in', $ids];
                    // $saleSeats = BusDriverAbilityService::mainModel()->where($cond)->group('bus_id')->column('count( DISTINCT driver_id ) AS number', 'bus_id');
                    // $etcCounts = BusHighwayFeeService::groupBatchCount('bus_id', $ids);
                    // $oilCounts = BusOilingService::groupBatchCount('bus_id', $ids);
                    // $ownCounts = BusOwnsService::groupBatchCount('bus_id', $ids);
                    // $breakRuleCounts = BusBreakRulesService::groupBatchCount('bus_id', $ids);
                    $cardCounts = CardService::groupBatchCount('belong_table_id', $ids);
                    // $fixCounts = BusFixService::groupBatchCount('bus_id', $ids);
                    $orderBaoBusCounts = OrderBaoBusService::groupBatchCount('bus_id', $ids);

                    $tmp            = BusDriverAbilityService::busDriverArrList();
                    $driverNamesArr = Arrays2d::fieldSetKey($tmp, 'bus_id');
                    
                    foreach ($lists as &$v) {
                        $busDriverInfo              = Arrays::value($driverNamesArr, $v['id']);

                        $v['driverNames']           = Arrays::value($busDriverInfo, 'driverName');
                        // 20230317:是否有司机
                        $v['hasDriver'] = $v['current_driver'] ? 1 : 0;
                        //驾驶员
                        // $v['abilityCounts'] = Arrays::value($saleSeats, $v['id'], 0);
                        //etc数
                        // $v['etcCounts'] = Arrays::value($etcCounts, $v['id'], 0);
                        //加油数
                        // $v['oilCounts'] = Arrays::value($oilCounts, $v['id'], 0);
                        //股比数
                        // $v['ownCounts'] = Arrays::value($ownCounts, $v['id'], 0);
                        //违章数
                        // $v['breakRuleCounts'] = Arrays::value($breakRuleCounts, $v['id'], 0);
                        //卡表数
                        $v['cardCounts'] = Arrays::value($cardCounts, $v['id'], 0);
                        //维修数
                        // $v['fixCounts'] = Arrays::value($fixCounts, $v['id'], 0);
                        //维修数
                        $v['orderBaoBusCounts'] = Arrays::value($orderBaoBusCounts, $v['id'], 0);
                        // 20231225：是否有定位设备
                        $v['hasGps'] = $v['equipment'] ? 1 : 0;
                    }

                    return $lists;
                },true);
    }

    /**
     * 20220829：车辆是否已对接gps定位系统
     */
    public function hasGps() {
        $info = $this->get();
        return $info && $info['equipment'];
    }

    /**
     * 20230525:车载定位链接
     */
    public function gpsLink() {

        $param['equipment'] = $this->fEquipment();
        $param['timestamp'] = time();

        $url = '/gps/bus/point';
        return Url::addParam($url, $param);
    }

    /**
     * 20221004
     * 线路牌号
     */
    public function getCertLineNo() {
        return Cachex::funcGet(__METHOD__ . $this->uuid, function() {
                    $con[] = ['cert_key', '=', 'line'];
                    $con[] = ['belong_table_id', '=', $this->uuid];
                    $info = CertService::certBatchFind($con);
                    // $info = CertService::where($con)->find();
                    return $info ? $info['cert_no'] : '';
                }, true, 10);
    }

    /**
     * 20221004：运输证号
     * @return type
     */
    public function getCertTransportNo() {
        return Cachex::funcGet(__METHOD__ . $this->uuid, function() {
                    $con[] = ['cert_key', '=', 'transport'];
                    $con[] = ['belong_table_id', '=', $this->uuid];
                    $info = CertService::certBatchFind($con);
                    // $info = CertService::where($con)->find();
                    return $info ? $info['cert_no'] : '';
                }, true, 10);
    }

    /**
     * 20230322：根据车型分组提取自有车数量
     */
    public static function selfBusCountByType($con = []) {
        $con[] = ['owner_type', '=', 'self'];
        $con[] = ['status', '=', 1];

        return self::where($con)->group('bus_type')->column('count(1)', 'bus_type');
    }

    /**
     * 20230322:自有车型车牌数组
     * @param type $con
     * @return type
     */
    public static function selfBusPlateStrByType($con = []) {
        $con[] = ['owner_type', '=', 'self'];
        $con[] = ['status', '=', 1];

        return self::where($con)->group('bus_type')
                        ->column('group_concat(licencePlateSeats)', 'bus_type');
    }
    /**
     * 全部自有车辆(含已失效)
     */
    public static function selfBusIds($con = []){
        $con[] = ['owner_type', '=', 'self'];
        return self::where($con)->column('id');
    }
    /**
     * 有效自有车辆
     * @param array $con
     * @return type
     */
    public static function selfBusIdsEffect($con = []){
        $con[] = ['status', '=', 1];
        return self::selfBusIds();
    }

    /**
     * 20230820：设置车辆的抽成率
     */
    public function setRateCondRam(){
        $info = $this->get();
        // 司机抽成率
        $rate = $info['default_driver_rate'] ? :0;
        // 固定前缀不重复
        $ruleInfo['prize_key']      = 'DRB_'.$this->uuid;
        $ruleInfo['prize_describe'] = Arrays::value($info, 'licencePlateSeats');

        // 固定写1吧
        $ruleInfo['per_prize']      = 1;
        $ruleInfo['rate']           = $rate;
        // 20231130;去除round
        $ruleInfo['cal_method']     = '';
        $ruleInfo['prize_cate']     = 'rate';

        // 条件
        $cond =[];
        $cond[] = [
            'group_id'=>1
            ,'judge_field'=>'bus_id'
            ,'judge_sign'=>'=='
            ,'judge_value'=>$this->uuid
        ];
        // 车小；车型大，小于0；
        $cond[] = [
            'group_id'      =>1
            ,'judge_field'  =>'isBusTypeLevelSame'
            ,'judge_sign'   =>'<='
            ,'judge_value'  =>0
        ];

        // 司机抽成率，车辆关联
        $groupKey = 'driverSalaryRateBus';
        return PrizeRuleService::setRuleRam($groupKey, $ruleInfo, $cond);
    }
    /**
     * 20231017：车辆是否归属同一个单位
     */
    public static function isSameCustomer($ids){
        $con            = [];
        $con[]          = ['id','in',$ids];
        $customerIds    = self::mainModel()->where($con)->column('distinct customer_id');

        return count($customerIds) <= 1;
    }
    /**
     * 当前司机
     * 20240403：调整逻辑：
     */
    public function currentDriverId(){
        $lists = $this->objAttrsList('busDriverAbility');
        return $lists ? $lists[0]['driver_id'] : $this->fCurrentDriver();
    }

}
