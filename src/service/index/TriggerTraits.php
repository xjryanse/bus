<?php
namespace xjryanse\bus\service\index;

use xjryanse\logic\Arrays;
use xjryanse\logic\DataCheck;
use xjryanse\system\logic\ConfigLogic;
use xjryanse\bus\service\BusTypeService;
use app\circuit\service\CircuitPlateService;
use app\cert\service\CertService;
use Exception;
/**
 * 触发复用
 */
trait TriggerTraits{

    public static function extraPreSave(&$data, $uuid) {
        $notice['licence_plate'] = '车牌号码必须';
        $notice['bus_type'] = '车型必须';
        $notice['busi_type'] = '运营类型必须';
        $notice['passenger_max'] = '客座数必须';
        $notice['owner_type'] = '车辆归属必须';
        DataCheck::must($data, array_keys($notice), $notice);
        // 20230328
        $con[] = ['licence_plate', '=', $data['licence_plate']];
        if (self::where($con)->count()) {
            throw new Exception('车牌号' . $data['licence_plate'] . '已存在不可添加');
        }

        //初始化车辆证件数据
        self::busCertInit($uuid);
        return $data;
    }

    public static function extraAfterSave(&$data, $uuid) {
        self::staticCacheClear();
    }

    public static function extraAfterUpdate(&$data, $uuid) {
        self::staticCacheClear();
    }

    public function extraPreDelete() {
        self::checkTransaction();

    }

    /**
     * 20230723
     * @param type $data
     * @param type $uuid
     * @return string
     * @throws Exception
     */
    public static function ramPreSave(&$data, $uuid) {
        // 20231208:版本车辆数校验
        $busLimitKey    = 'compMaxBusLimit';
        $busLimit       = ConfigLogic::config($busLimitKey);
        if($busLimit && self::where()->count() >= $busLimit){
            throw new Exception('当前版本最多可设置'.$busLimit.'辆车，如需设置更多车辆，请联系客服升级版本');
        }
        if(Arrays::value($data, 'seats')){
            $data['passenger_max'] = $data['seats'] - 1;
            // 20231230:增加座位取车型
            if(!Arrays::value($data, 'bus_type')){
                $data['bus_type'] = BusTypeService::seatsGetId($data['seats']);
                if(!$data['bus_type']){
                    throw new Exception('没有'.$data['seats'].'座的车型，请先添加');
                }
            }
        }

        $notice['licence_plate'] = '车牌号码必须';
        $notice['bus_type']      = '车型必须';
//        $notice['busi_type']     = '运营类型必须';
//        $notice['passenger_max'] = '客座数必须';
//        $notice['owner_type'] = '车辆归属必须';
        DataCheck::must($data, array_keys($notice), $notice);
        if(!Arrays::value($data, 'passenger_max')){
            $busTypeInfo = BusTypeService::getInstance($data['bus_type'])->get();
            $data['passenger_max'] = $busTypeInfo['passenger_max'];
        }
        if(!Arrays::value($data, 'owner_type')){
            $data['owner_type'] = 'self';
        }

        // 20230328
        $con[] = ['licence_plate', '=', $data['licence_plate']];
        if (self::where($con)->count()) {
            throw new Exception('车牌号' . $data['licence_plate'] . '已存在不可添加');
        }

        //初始化车辆证件数据
        self::busCertInit($uuid);
        return $data;
    }
    
    /**
     * 20230723
     * @param type $data
     * @param type $uuid
     * @return string
     * @throws Exception
     */
    public static function ramAfterUpdate(&$data, $uuid) {
        self::getInstance($uuid)->setRateCondRam();
    }
    
    public function ramPreDelete() {
        // 删除证件TODO
        $conC    = [];
        $conC[]  = ['belong_table','=','w_bus'];
        $conC[]  = ['belong_table_id','=',$this->uuid];
        CertService::where($conC)->delete();
        // 删除线路牌
        $conP    = [];
        $conP[]  = ['bus_id','=',$this->uuid];
        CircuitPlateService::where($conP)->delete();
    }
}
