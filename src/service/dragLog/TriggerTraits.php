<?php
namespace xjryanse\bus\service\dragLog;

use xjryanse\logic\Arrays;
use xjryanse\bus\service\BusService;
use xjryanse\logic\DataCheck;
/**
 * 
 */
trait TriggerTraits{
    /**
     * 钩子-保存前
     */
    public static function extraPreSave(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }

    /**
     * 钩子-更新前
     */
    public static function extraPreUpdate(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }

    /**
     * 钩子-删除前
     */
    public function extraPreDelete() {
        self::stopUse(__METHOD__);
    }
    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        // 20240504
        $keys = ['bus_id'];
        $notice['bus_id'] = '车辆必须';
        DataCheck::must($data, $keys, $notice);

        self::redunFields($data, $uuid);
        // 手机端才处理

//        if(session(SESSION_SOURCE) != 'admin'){
//            if(!Arrays::value($data, 'driver_id')){
//                $data['driver_id'] = session(SESSION_USER_ID);
//            }
//            if(!Arrays::value($data, 'wash_time')){
//                $data['wash_time'] = date('Y-m-d H:i:s');
//            }
//        }
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
        // 差异数组
        // self::getInstance($uuid)->updateStatementOrder();
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        // $this->financeCommClearStatementOrder();
    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete() {
        
    }
    
    protected static function redunFields(&$data, $uuid){
        $info = self::getInstance($uuid)->get();

        $busId = Arrays::value($info, 'bus_id');

        $data['dept_id'] = BusService::getInstance($busId)->fDeptId();

        return $data;
    }
}
