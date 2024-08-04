<?php
namespace xjryanse\bus\service\highwayFee;

use app\order\service\OrderBaoBusService;
use xjryanse\logic\Arrays;
use Exception;
/**
 * 触发器
 */
trait DoTraits{

    /**
     * 按订单时间
     */
    public function doBindByOrderTime() {
        $info   = $this->get();
        $busId  = Arrays::value($info, 'bus_id');
        $time   = Arrays::value($info, 'end_time');

        $lists = OrderBaoBusService::calInBaoBusListByBusAndTime($busId, $time);
        if(!$lists){
            throw new Exception('没有匹配的订单'.$time);
        }
        if(count($lists) > 1){
            throw new Exception('关联'.count($lists).'个趟次，无法匹配，需人工识别');
        }
        
    }
    /**
     * 按实际时间
     * @throws Exception
     */
    public function doBindByRealTime() {
        $info   = $this->get();
        $busId  = Arrays::value($info, 'bus_id');
        $time   = Arrays::value($info, 'end_time');

        $lists = OrderBaoBusService::calInBaoBusListByBusAndTimeReal($busId, $time);
        if(!$lists){
            throw new Exception('没有匹配的订单'.$time);
        }
        if(count($lists) > 1){
            throw new Exception('关联'.count($lists).'个趟次，无法匹配，需人工识别');
        }
        $item                   = $lists[0];
        $upData['order_id']     = Arrays::value($item, 'order_id');
        $upData['bao_bus_id']   = Arrays::value($item, 'id');
        return $this->doUpdateRam($upData);
    }

}
