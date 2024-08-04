<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;

/**
 * 车辆违章
 */
class BusBreakRulesBatchService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    // use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusBreakRulesBatch';

    use \xjryanse\bus\service\breakRulesBatch\TriggerTraits;
    
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use ($ids) {
                    return $lists;
                }, true);
    }
    
}
