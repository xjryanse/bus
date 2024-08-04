<?php
namespace xjryanse\bus\service\oiling;

use xjryanse\logic\Arrays;
/**
 * 计算逻辑
 */
trait ListTraits{
    
    /**
     * 获取指定订单的加油记录
     * 20231209
     */
    public static function listBaoBusOil($param){
        $orderId    = Arrays::value($param, 'order_id');
        $baoBusId   = Arrays::value($param, 'bao_bus_id');
        $con = [];
        $con[] = ['order_id','=',$orderId];
        $con[] = ['bao_bus_id','=',$baoBusId];
        
        $res = self::where($con)->select();
        return $res ? $res->toArray() : [];
    }
    
}
