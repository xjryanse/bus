<?php
namespace xjryanse\bus\service\type;

use xjryanse\logic\Arrays;
use xjryanse\prize\service\PrizeGroupService;
use xjryanse\prize\service\PrizeRuleService;

/**
 * 触发复用
 */
trait PaginateTraits{
    /**
     * 20231212:用于后台针对现有车型进行抽成率配置
     * 
     * @param type $data
     * @param type $uuid
     */
    public static function paginateForRate($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false) {

        $con[] = ['status','=',1];
        $order = 'passenger_max desc';
        $res = self::paginateX($con, $order, $perPage, $having, $field, $withSum);
        
        $groupCate = 'driverSalaryRate';
        $groupIds = PrizeGroupService::cateToIds($groupCate);
        // 20231128:数组
        $arr = PrizeRuleService::getPrizeKeyCountByGroupId($groupIds);

        foreach($res['data'] as &$v){
            // 司机车型抽点
            $v['prizeKey']          = 'DRBT_'.$v['id'];
            $v['prizeRuleCount']    = Arrays::value($arr, $v['prizeKey']) ? : 0;
        }

        return $res;
    }

}
