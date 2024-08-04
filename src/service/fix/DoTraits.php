<?php

namespace xjryanse\bus\service\fix;

use app\gps\service\GpsJt808PlaceService;
/**
 * 
 */
trait DoTraits{
    /**
     * 20231114:上报加油，替换webapi/ABus/uplOil方法
     * @return type
     */
    public static function doUpl($param){
        // $data = $param;
        $data = isset($param['table_data']) ? $param['table_data'] : $param;
        // $data                   = $this->data;
        // 【1】记录的更新
        if($data['id']){
            // 202211-16:一般用于补充上报公里数和加油表拍照
            return self::getInstance($data['id'])->updateRam($data);
        }

        // 提取车载定位公里数
        $data['gps_mile']       = GpsJt808PlaceService::getBusKilometer($data['bus_id']);

        return self::saveRam($data);
    }
    
}
