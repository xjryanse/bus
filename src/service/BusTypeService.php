<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Cachex;
use xjryanse\logic\Arrays;
use app\order\service\OrderBaoBusService;
use xjryanse\prize\service\PrizeRuleService;
use Exception;

/**
 * 
 */
class BusTypeService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusType';

    use \xjryanse\bus\service\type\PaginateTraits;
    use \xjryanse\bus\service\type\FieldTraits;
    
    public static function extraDetails($ids) {
        //数组返回多个，非数组返回一个
        $isMulti = is_array($ids);
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $con[] = ['id', 'in', $ids];
        $listRaw = self::mainModel()->where($con)->select();
        $lists = $listRaw ? $listRaw->toArray() : [];
        $busCountsArr = BusService::groupBatchCount('bus_type', $ids);
        $orderBaoBusArr = OrderBaoBusService::groupBatchCount('bus_type_id', $ids);

        foreach ($lists as &$v) {
            //车辆数
            $v['busCount'] = Arrays::value($busCountsArr, $v['id'], 0);
            //包车数
            $v['orderBaoBusCount'] = Arrays::value($orderBaoBusArr, $v['id'], 0);
        }
        return $isMulti ? $lists : $lists[0];
    }

    /**
     * 钩子-删除前
     */
    public function extraPreDelete() {
        self::checkTransaction();
        $con[] = ['bus_type', '=', $this->uuid];
        $bus = BusService::mainModel()->master()->where($con)->count(1);
        if ($bus) {
            throw new Exception('该车型有车辆，不可删除');
        }

        $cond[] = ['bus_type_id', '=', $this->uuid];
        $OrderBaoBus = OrderBaoBusService::mainModel()->master()->where($cond)->count(1);
        if ($OrderBaoBus) {
            throw new Exception('该车型有趟次记录，不可删除');
        }
    }

    /**
     * 车型取id
     * @param type $busType 
     */
    public static function busTypeGetId($busType) {
        return Cachex::funcGet(__CLASS__ . __FUNCTION__ . $busType, function() use ($busType) {
                    $con[] = ['bus_type', '=', $busType];
                    $con[] = ['company_id', '=', session(SESSION_COMPANY_ID)];
                    return self::mainModel()->where($con)->value('id');
                }, true);
    }
    /**
     * 20231230：座位号取车型
     */
    public static function seatsGetId($seats){
        $passengerMax = $seats - 1;
        $con[] = ['passenger_max', '=', $passengerMax];
        $info = self::staticConFind($con);
        return $info ? $info['id'] : '';
    }

    /**
     * 车型数组，获取最大乘客数
     */
    public static function busTypeArrMaxPassengerCount($busTypes) {
        $busTypesArr = self::staticConList();
        $passengerMaxArr = array_column($busTypesArr, 'passenger_max', 'id');
        foreach ($busTypes as &$v) {
            $v['passenger_max'] = Arrays::value($passengerMaxArr, $v['bus_type_id'], 0) * $v['number'];
        }
        return array_sum(array_column($busTypes, 'passenger_max'));
    }

    /**
     * 2023012：前端车型数组，提取车辆数
     * @param type $busTypes
      ['bus_type_id'=>'5308239509649002496','number'=>1]
     * @return type
     */
    public static function busTypeArrGetCount($busTypes) {
        return array_sum(array_column($busTypes, 'number'));
    }


    /**
     * 20231212：设置车型的抽成率
     */
    public function setRateCondRam(){
        $info = $this->get();
        // 司机抽成率
        $rate = $info['driver_rate'] ? :0;
        // 固定前缀不重复
        $ruleInfo['prize_key']      = 'DRBT_'.$this->uuid;
        $ruleInfo['prize_describe'] = Arrays::value($info, 'bus_type');

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
            ,'judge_field'=>'bus_type_id'
            ,'judge_sign'=>'=='
            ,'judge_value'=>$this->uuid
        ];
        // 车型级别不同，才提取车型的提成率
        // 车大，车型小，大于0
        $cond[] = [
            'group_id'      =>1
            ,'judge_field'  =>'isBusTypeLevelSame'
            ,'judge_sign'   =>'>'
            ,'judge_value'  =>0
        ];
        
        // 司机抽成率，车辆关联
        $groupKey = 'driverSalaryRateBus';
        return PrizeRuleService::setRuleRam($groupKey, $ruleInfo, $cond);
    }
    /**
     * 计算车型的座位数
     */
    public function calSeats(){
        $info = $this->get();
        $seats = $info['passenger_max'] +1;

        return $seats;
    }
    
}
