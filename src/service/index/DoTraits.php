<?php
namespace xjryanse\bus\service\index;

use xjryanse\logic\Arrays;
use app\circuit\service\CircuitPlateService;
use xjryanse\bus\service\BusTimelyFeeService;
use app\cert\service\CertService;
/**
 * 触发复用
 */
trait DoTraits{

    /**
     * 20231230:带线路牌导入车辆信息
     * TODO:改造成通用方法
     *  SystemImportTemplateService::getInstance($templateId)->doImportData($param);
     * @param type $param
     */
    public static function doImportWithCircuitPlate($param){
        $tableData  = Arrays::value($param, 'table_data') ? : [];
        $certBelongTable = self::getTable();
        // Debug::dump($tableData);
        foreach($tableData as $dItem){
            $tBusData       = Arrays::getByKeys($param,['dept_id','owner_type']);
            $iBusData       = Arrays::getByKeys($dItem,['licence_plate','seats','bus_cate','tech_cate','bus_brand','bus_model','bus_fuel','sort']);
            $tBus           = array_merge($tBusData, $iBusData);
            // 【数据1】：去车辆表          
            // $busId          = self::saveGetIdRam($tBus);
            
            $busId = self::commGetIdEG(['licence_plate'=>$tBus['licence_plate'],'seats'=>$tBus['seats']]);            
            self::getInstance($busId)->updateRam($tBus);
            // 车辆id,提取线路牌id
            $circuitPlateId = CircuitPlateService::busIdGetIdEG($busId);
            // 线路牌的
            $tCpData        = Arrays::getByKeys($param,['circuit_type']);
            $cpKeys         = ['brand_no','circuit_name','miles','times','from_station_name','to_station_name','main_pass_station','main_stop_station','lineLimitTime'];
            $cpData         = Arrays::getByKeys($dItem, $cpKeys);
            $cpData['end_time'] = Arrays::value($cpData, 'lineLimitTime') ?:null;

            $tCp            = array_merge($tCpData, $cpData);
            // 【数据2】：去线路牌表
            CircuitPlateService::getInstance($circuitPlateId)->updateRam($tCp);
            // 写入证件的:行驶证，许可证，运输证
            $certKeys = ['vehicle','transport','carriers','commer','insure'];
            foreach($certKeys as $cKey){
                $certId = CertService::belongTableIdAndKeyGetIdEG($certBelongTable, $busId, $cKey);
                // 当前证件的三个key
                $iKeys = [
                    $cKey.'CertTime'        => 'cert_time',
                    $cKey.'CertNo'          => 'cert_no',
                    $cKey.'LimitTime'       => 'cert_limit_time',
                    $cKey.'NextAuditTime'   => 'next_audit_time'
                ];
                
                if(Arrays::hasKeys($dItem, array_keys($iKeys))){
                    // 有数据，就更新这些表
                    $cData = Arrays::keyReplace($dItem, $iKeys);
                    // 【数据3】：去证件表
                    CertService::getInstance($certId)->updateRam($cData);
                }
            }
        }

        return true;
    }
    
    
    /**
     * 初始化车辆管理费用
     */
    public function doInitBusManageFee($param){
        $prize = Arrays::value($param, 'prize') 
                ? : $this->fMngPrize();
        if(!$prize){
            return false;
        }
        $yearmonth = Arrays::value($param, 'yearmonth') ? : date('Y-m');
        return BusTimelyFeeService::initBusManageFee($this->uuid, $prize, $yearmonth);
    }

}
