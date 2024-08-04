<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;

/**
 * 车辆维修申请
 */
class BusFixItemService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    use \xjryanse\traits\FinanceSourceModelTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusFixItem';

//    use \xjryanse\bus\service\fixItem\FieldTraits;
    use \xjryanse\bus\service\fixItem\TriggerTraits;
//    use \xjryanse\bus\service\fixItem\ListTraits;
//    use \xjryanse\bus\service\fixItem\DoTraits;
//    use \xjryanse\bus\service\fixItem\PaginateTraits;

    /**
     * 额外详情
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    $con    = [];
                    $con[]  = ['item_name','in',Arrays2d::uniqueColumn($lists, 'item_name')];
                    $arr    = BusFixItemStandardService::where($con)->column('unit_prize','item_name');

                    foreach($lists as &$v){
                        // 20240324:合同单价
                        $v['standardUnitPrize'] = Arrays::value($arr, $v['item_name']) 
                                ? round(Arrays::value($arr, $v['item_name'])) 
                                : Arrays::value($arr, $v['item_name']);
                    }

                    return $lists;
                }, true);
    }
    
}
