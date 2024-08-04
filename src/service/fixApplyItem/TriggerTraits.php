<?php
namespace xjryanse\bus\service\fixApplyItem;

use xjryanse\logic\Arrays;
use xjryanse\logic\Debug;
use xjryanse\bus\service\BusFixApplyService;
use xjryanse\bus\service\BusFixItemStandardService;
use Exception;

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
        if(!Arrays::value($data, 'number')){
            // 空的用于redunFields计价
            $data['number'] = 1;
        }
        if(!is_numeric($data[ 'number' ])){
            throw new Exception('数量应是数字');
        }
        
        self::redunFields($data, $uuid);
        $data['fix_img'] = Arrays::value($data, 'fix_img') ? $data['fix_img'] : '';        
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        $info = self::getInstance($uuid)->get();
        if ($info['apply_id']) {
            BusFixApplyService::getInstance($info['apply_id'])->feeMoneyUpdateRam();
        }
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
        $info = self::getInstance($uuid)->get();
        if ($info['apply_id']) {
            BusFixApplyService::getInstance($info['apply_id'])->feeMoneyUpdateRam();
        }
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {

    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete($info) {
        if ($info['apply_id']) {
            BusFixApplyService::getInstance($info['apply_id'])->feeMoneyUpdateRam();
        }
    }
    
    protected static function redunFields(&$data, $uuid){
        if(isset($data['unit_prize']) && isset($data['number'])){
            // 20240316：数量为空，默认用1算
            $number = $data['number'] == '' ? 1 : $data['number'];
            $data['total_prize'] = $data['unit_prize'] * $number;
        }
        // 20240521
        $fixApplyId = self::getInstance($uuid)->fApplyId();
        if($fixApplyId){
            $customerId = BusFixApplyService::getInstance($fixApplyId)->fFixCustomerId();
            $time       = BusFixApplyService::getInstance($fixApplyId)->fApplyTime();
            // Debug::dump($customerId);
            $itemName = self::getInstance($uuid)->fItemName();
            $data['item_standard_id'] = BusFixItemStandardService::matchItem($customerId, $time, $itemName);
        }
        
        return $data;
    }
}
