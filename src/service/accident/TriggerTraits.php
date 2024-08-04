<?php
namespace xjryanse\bus\service\accident;

use xjryanse\logic\Arrays;
use xjryanse\bus\service\BusService;
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
        
    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete() {
        
    }

    protected static function redunFields(&$data, $uuid){
        if(Arrays::value($data, 'bus_id') && !Arrays::value($data, 'dept_id')){
            $data['dept_id'] = BusService::getInstance($data['bus_id'])->fDeptId();
        }

        return $data;
    }
}
