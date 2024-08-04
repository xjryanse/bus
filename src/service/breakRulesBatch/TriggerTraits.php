<?php
namespace xjryanse\bus\service\breakRulesBatch;

use xjryanse\bus\service\BusBreakRulesService;
/**
 * 触发复用
 */
trait TriggerTraits{

    public static function extraPreSave(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }

    public static function extraPreUpdate(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }

    public function extraPreDelete() {
        self::stopUse(__METHOD__);
    }
    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        self::redunFields($data, $uuid);
        if(isset($data['dtl_ids'])){
            $dtlIds = is_array($data['dtl_ids']) ? $data['dtl_ids'] : explode(',', $data['dtl_ids']);
            foreach($dtlIds as $dtlId){
                BusBreakRulesService::getInstance($dtlId)->updateRam(['batch_id'=>$uuid]);
            }
        }
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        
    }

    /**
     * 钩子-更新前
     */
    public static function ramPreUpdate(&$data, $uuid) {
        self::redunFields($data, $uuid);
    }

    /**
     * 钩子-更新后
     */
    public static function ramAfterUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        $lists = $this->objAttrsList('busBreakRules');
        foreach($lists as $v){
            BusBreakRulesService::getInstance($v['id'])->updateRam(['batch_id'=>'']);
        }
    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete() {
        
    }

    protected static function redunFields(&$data, $uuid){

        return $data;
    }
}
