<?php
namespace xjryanse\bus\service\fix;

use xjryanse\logic\Arrays;
use xjryanse\bus\service\BusService;
/**
 * 
 */
trait TriggerTraits{
    /**
     * 钩子-保存前
     */
    public static function extraPreSave(&$data, $uuid) {

    }

    /**
     * 钩子-保存后
     */
    public static function extraAfterSave(&$data, $uuid) {

    }

    /**
     * 钩子-更新前
     */
    public static function extraPreUpdate(&$data, $uuid) {

    }

    /**
     * 钩子-更新后
     */
    public static function extraAfterUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-删除前
     */
    public function extraPreDelete() {
        
    }

    /**
     * 钩子-删除后
     */
    public function extraAfterDelete() {
        
    }
    
    
    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        self::redunFields($data, $uuid);
        // 手机端才处理
        if(session(SESSION_SOURCE) != 'admin'){
            if(!Arrays::value($data, 'driver_id')){
                $data['driver_id'] = session(SESSION_USER_ID);
            }
            if(!Arrays::value($data, 'fix_time')){
                $data['fix_time'] = date('Y-m-d H:i:s');
            }
        }
        // 20231231:付款人
        if(!Arrays::value($data, 'payer_id')){
            $data['payer_id'] = $data['driver_id'];
        }
        
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        // 20231217
        if($data['prize']){
            self::getInstance($uuid)->addStatementOrder();
        }
        
        // 20240409：同步报销数据
        self::getInstance($uuid)->staffFeeSync();
    }

    /**
     * 钩子-更新前
     */
    public static function ramPreUpdate(&$data, $uuid) {
        self::redunFields($data, $uuid);
        // self::getInstance($uuid)->feeMoneyUpdateRam();
    }

    /**
     * 钩子-更新后
     */
    public static function ramAfterUpdate(&$data, $uuid) {
        // 差异数组
        self::getInstance($uuid)->updateStatementOrder();
        // 20240409：同步报销数据
        self::getInstance($uuid)->staffFeeSync();
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        $this->financeCommClearStatementOrder();
    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete() {
        // 20240409：同步报销数据
        $this->staffFeeSync();
    }
    
    protected static function redunFields(&$data, $uuid){
        $info = self::getInstance($uuid)->get();

        $busId = Arrays::value($info, 'bus_id');

        $data['dept_id']    = BusService::getInstance($busId)->fDeptId();

        if(isset($data['prize']) || isset($data['parts_prize'])){
            // 工时价格
            $workPrize  = isset($data['prize']) ? $data['prize'] : Arrays::value($info, 'prize',0);
            // 配件价格
            $partsPrize = isset($data['parts_prize']) ? $data['parts_prize'] : Arrays::value($info, 'parts_prize',0);
            
            $data['prize_all'] = $workPrize + $partsPrize;
        }
        // 20240409·
        if(self::getInstance($uuid)->objAttrsList('busFixItem')){
            $data['prize'] = self::getInstance($uuid)->calFeeMoney();
        }

        return $data;
    }
}
