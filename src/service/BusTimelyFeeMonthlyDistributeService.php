<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
// use xjryanse\logic\Datetime;
// use xjryanse\bus\service\BusService;
use Exception;
/**
 * 车辆按时段费用
 * monthlyManage:车辆管理月费
 * 
 */
class BusTimelyFeeMonthlyDistributeService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    // use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    // use \xjryanse\traits\FinanceSourceModelTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusTimelyFeeMonthlyDistribute';

    /**
     * 额外详情
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    return $lists;
                }, true);
    }

}
