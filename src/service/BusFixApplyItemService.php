<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
/**
 * w_bus_fix_apply_item
 */
class BusFixApplyItemService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;
    use \xjryanse\traits\ObjectAttrTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusFixApplyItem';
    //直接执行后续触发动作
    protected static $directAfter = true;
    
    use \xjryanse\bus\service\fixApplyItem\TriggerTraits;
    use \xjryanse\bus\service\fixApplyItem\FieldTraits;

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    return $lists;
                }, true);
    }
    
    /**
     * 20240521:同步关联
     */
    public static function itemStandardSync(){
        // 提取有用的项目        
        $itemId = self::mainModel()->alias('a')
                ->join('w_bus_fix_item_standard b','a.item_name = b.item_name')
                ->column('distinct a.id');
        
        foreach($itemId as $id){
            self::getInstance($id)->updateRam(['status'=>1]);
        }
    }
    
}
