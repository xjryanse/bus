<?php
namespace xjryanse\bus\service\highwayFee;

use app\order\service\OrderBaoBusService;
use app\bus\service\ViewBusCardEtcService;
use Exception;
/**
 * 触发器
 */
trait TriggerTraits{

    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        if (!Arrays::value($data, 'bus_id')) {
            $data['bus_id'] = ViewBusCardEtcService::cardNoBusId($data['card']);
        }
        if (!$data['bus_id']) {
            // 20230203
            throw new Exception('ETC卡:' . $data['card'] . ' 未绑定车辆，请先绑定');
        }
        if (isset($data['pay_prize'])) {
            $data['pay_prize'] = str_replace(',', '', $data['pay_prize']);
            $data['pay_prize'] = str_replace('￥', '', $data['pay_prize']);
        }
        if (isset($data['prize'])) {
            $data['prize'] = str_replace(',', '', $data['prize']);
            $data['prize'] = str_replace('￥', '', $data['prize']);
        }
        if (isset($data['refund_prize'])) {
            $data['refund_prize'] = str_replace(',', '', $data['refund_prize']);
            $data['refund_prize'] = str_replace('￥', '', $data['refund_prize']);
        }
        // 更新一些冗余
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
        
        // 更新一些冗余
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
        $busId = isset($data['bus_id']) 
                ? $data['bus_id'] 
                : self::getInstance($uuid)->fBusId();
        $endTime = isset($data['end_time']) 
                ? $data['end_time'] 
                : self::getInstance($uuid)->fEndTime();

        if($busId && $endTime){
            $lists = OrderBaoBusService::calInBaoBusListByBusAndTimeReal($busId, $endTime);
            if($lists){
                $info               = $lists[0];
                $data['order_id']   = $info['order_id'];
                $data['bao_bus_id'] = $info['id'];
            }
        }
        return $data;
    }
}
