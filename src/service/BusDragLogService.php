<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;

/**
 * 
 */
class BusDragLogService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\MainStaticsTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusDragLog';

    use \xjryanse\bus\service\dragLog\DoTraits;
    use \xjryanse\bus\service\dragLog\PaginateTraits;
    use \xjryanse\bus\service\dragLog\FieldTraits;
    use \xjryanse\bus\service\dragLog\TriggerTraits;
    
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    foreach ($lists as &$v) {
                        // 是否本人的报销单
                        // $v['driverIsMe'] = $v['driver_id'] == session(SESSION_USER_ID) ? 1:0;
                    }

                    return $lists;
                }, true);
    }
}
