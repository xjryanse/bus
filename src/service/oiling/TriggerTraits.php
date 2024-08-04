<?php
namespace xjryanse\bus\service\oiling;

use xjryanse\logic\Arrays;
use xjryanse\logic\Debug;
use xjryanse\logic\DataCheck;
use app\gps\service\GpsJt808PlaceService;
use xjryanse\bus\service\BusMileService;
/**
 * 触发器
 */
trait TriggerTraits{

    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        $numNotice['prize']     = '加油金额需为数字';
        $numNotice['kilometer'] = '公里数需为数字';
        $numNotice['number']    = '加油升数需为数字';
        DataCheck::isNumber($data, ['number','prize','kilometer'], $numNotice);

        $keys = ['bus_id'];
        DataCheck::must($data, $keys);
        if ($data['number'] > 1000) {
            throw new Exception('加油升数太大');
        }
        if ($data['prize'] > 10000) {
            throw new Exception('加油金额太大');
        }
        //加油人
        if (!Arrays::value($data, 'driver_id')) {
            $data['driver_id'] = session(SESSION_USER_ID);
        }
        // 加油时间
        if (!Arrays::value($data, 'time')) {
            $data['time'] = date('Y-m-d H:i:s');
        }
        // 新增时，写入末次加油id
        $data['last_id'] = self::lastOilingId($data['bus_id']);
        // 更新一些冗余
        self::redunFields($data, $uuid);

        // 20231231:付款人
        if(!Arrays::value($data, 'payer_id')){
            $data['payer_id'] = $data['driver_id'];
        }
        // 20240528修bug
        // Debug::dump($data);
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        $dataUpd = self::getInstance($uuid)->calLastFullData();
        self::getInstance($uuid)->updateRam($dataUpd);
        
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
        
        // 更新一些冗余
        self::redunFields($data, $uuid);
    }

    /**
     * 钩子-更新后
     */
    public static function ramAfterUpdate(&$data, $uuid) {
        $info = self::getInstance($uuid)->get();
        // 20230318：循环更新该车时间在当前加油时间之后的所有加油记录
        $conBus[] = ['bus_id', '=', $info['bus_id']];
        $conBus[] = ['time', '>=', $info['time']];
        $ids = self::where($conBus)->order('time')->column('id');

        foreach ($ids as $id) {
            //20221116
            $dataUpd = self::getInstance($id)->calLastFullData();

            self::getInstance($id)->doUpdateRam($dataUpd);
        }
        
        // 20231205
        self::getInstance($uuid)->mileSync($dataUpd);
        
        // 差异数组
        self::getInstance($uuid)->updateStatementOrder();
        
        // 20240409：同步报销数据
        self::getInstance($uuid)->staffFeeSync();
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        $fromTable      = self::getTable();
        $fromTableId    = $this->uuid;
        BusMileService::deleteByFromTableAndFromTableId($fromTable, $fromTableId);
        
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
        // 车辆
        $busId  = isset($data['bus_id']) 
                ? $data['bus_id'] 
                : self::getInstance($uuid)->fBusId();
        // 加油时间
        $time   = isset($data['time']) 
                ? $data['time'] 
                : self::getInstance($uuid)->fTime();
        if($busId){
            // 上次加油记录id
            $lastId                 = self::lastOilingId($busId, $time);
            $data['last_id']        = $lastId;
            // 上次加满记录id
            $data['last_full_id']   = self::lastFullId($busId, $time);

            // 上次加油公里数
            $data['last_kilometer'] = self::getInstance($lastId)->fKilometer();
            // 上次加油定位器里程
            $data['last_gps_mile']  = self::getInstance($lastId)->fGpsMile();
            // 上次加油时间
            $data['last_time']      = self::getInstance($lastId)->fTime() ? : null;
        }
        
        if (intval(Arrays::value($data, 'prize')) && intval(Arrays::value($data, 'number'))) {
            $data['unit_price'] = round(Arrays::value($data, 'prize') / Arrays::value($data, 'number'), 2);
        }
        
        // 定位里程
        $gpsMile  = isset($data['gps_mile']) 
                ? $data['gps_mile'] 
                : self::getInstance($uuid)->fGpsMile();
        // 20231114: 0.00 处理

        if(!intval($gpsMile)){
            // 获取定位数据更新
            $gpsMile = GpsJt808PlaceService::getBusKilometer($busId, $time );
            // 有才更新，避免误写
            if($gpsMile){
                $data['gps_mile']       = $gpsMile;
            }
        }
        
        return $data;
    }
}
