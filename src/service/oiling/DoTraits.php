<?php

namespace xjryanse\bus\service\oiling;

use xjryanse\bus\service\BusOilingBaoBusService;
use xjryanse\logic\SnowFlake;
use xjryanse\logic\Arrays;
use xjryanse\bus\service\BusMileService;
use app\gps\service\GpsJt808PlaceService;
use Exception;
/**
 * 
 */
trait DoTraits{
    /**
     * 关联包车趟次
     * @createTime 2023-11-03
     */
    public function doOilingBaoBusGenerate(){

        $res = BusOilingBaoBusService::generateByBusOilingId($this->uuid);
        return $res;
    }
    /**
     * 写入里程上报表
     * @return type
     * @throws Exception
     */
    public function doMileAdd(){
        return $this->mileSync();
    }

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
        // 【2】校验是否重复上报
        $lastOilingId = self::lastOilingId($data['bus_id']);
        if($lastOilingId){
            $lastTimestamp = SnowFlake::getTimestamp($lastOilingId);
            if(time() - $lastTimestamp < 10){
                throw new Exception('加油记录已上报');
            }
            $lastOilingInfo = self::getInstance($lastOilingId)->get();
            if($lastOilingInfo['kilometer'] && $data['kilometer'] && abs($lastOilingInfo['kilometer'] - $data['kilometer']) < 10){
                throw new Exception('加油记录已经填写过了:公里:'.$data['kilometer'].'|升数:'.$data['number'].'|金额:'.$data['prize']);
            }
        }
        
        // 提取车载定位公里数
        $data['gps_mile']       = GpsJt808PlaceService::getBusKilometer($data['bus_id']);
        // 【3】匹配填报记录
        $matchLastId            = self::matchLastId($data['bus_id'], $data['number'], $data['prize']);
        if($matchLastId){
            if (!Arrays::value($data, 'driver_id')) {
                $data['driver_id'] = session(SESSION_USER_ID);
            }
            return self::getInstance($matchLastId)->updateRam($data);            
        }
        
        return self::saveRam($data);            
    }
    
}
