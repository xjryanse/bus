<?php
namespace xjryanse\bus\service\breakRules;

use xjryanse\logic\Arrays;
use Exception;
/**
 * 触发复用
 */
trait TriggerTraits{

    public static function extraPreSave(&$data, $uuid) {

    }

    public static function extraPreUpdate(&$data, $uuid) {

    }

    public function extraPreDelete() {

    }
    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        self::redunFields($data, $uuid);
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
        if(self::getInstance($uuid)->updateDiffsHasField(['batch_id'])){
            $batchIdDiffs = Arrays::value(self::$updateDiffs, 'batch_id') ? : [];
            if($batchIdDiffs[0] && $batchIdDiffs[1] && $batchIdDiffs[0] != $batchIdDiffs[1]){
                throw new Exception('您选择的记录已累积多次处理，不可重复操作');
            }
        }
        
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        $info = $this->get();
        if(Arrays::value($info, 'batch_id')){
            throw new Exception('已关联累积多次处理记录，请先删除');
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
