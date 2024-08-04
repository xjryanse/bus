<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;
use xjryanse\bus\service\BusService;
use xjryanse\logic\DbOperate;
use app\gps\service\GpsJt808PlaceService;
use app\bus\service\ViewBusCardOilService;
use Exception;

/**
 * 
 */
class BusOilingSinopecService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\MainStaticsTrait;
    use \xjryanse\traits\StaticsModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusOilingSinopec';
    //直接执行后续触发动作
    protected static $directAfter = true;

    use \xjryanse\bus\service\oilingSinopec\FieldTraits;

    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use ($ids) {
                    $oCounts = BusOilingService::groupBatchCount('source_id', array_column($lists, 'id'));
                    foreach ($lists as &$v) {
                        // 关联的加油记录数
                        $v['oCount'] = Arrays::value($oCounts, $v['id'], 0);
                    }
                    return $lists;
                });
    }

    
    /**
     * 批量转写加油总表
     */
    public static function addToOilingBatch(){
        // 提取不在总表中的记录


    }

    protected function addToOilingRam($data){
        
    }
    
    
    /**
     * 因中石化导出数据比较杂，此处用于过滤一些没用的数据
     * @param type $dataArr
     */
    public static function saveAllFilter(&$dataArr) {
        $dataKeyArr = ['加油', '消费', '优惠消费'];
        foreach ($dataArr as $k => $v) {
            if (!in_array($v['bill_type'], $dataKeyArr)) {
                unset($dataArr[$k]);
            }
        }
        return $dataArr;
    }

    /**
     * 2023-02-28：只保存加油记录
     * @param int $data
     * @param type $uuid
     */
    public static function extraPreSave(&$data, $uuid) {
        $data['bus_id'] = ViewBusCardOilService::cardNoBusId($data['card_no']);
        if ($data['bill_type'] == '加油') {
            $data['bill_type'] = 2;
        }

        if (!$data['bus_id']) {
            // 20230203
            throw new Exception('加油卡:' . $data['card_no'] . ' 未绑定车辆，请先绑定');
        }

        return $data;
    }

    /*     * *** 2023-02-28数据统计 ***************************** */

    protected static function staticsFields() {
        $fields[] = "bus_id";
        // 趟数
        $fields[] = "count(1) AS `feeCount`";
        $fields[] = "sum( prize ) AS `allMoney`";
        return $fields;
    }

    protected static function staticsDynArr() {
        $tableName = BusService::getTable();
        $dynArrs['bus_id'] = 'table_name=' . $tableName . '&key=id&value=licencePlateSeats';
        return $dynArrs;
    }

    public static function monthlyStatics($yearmonth, $moneyType) {
        $moneyTypeN = $moneyType ?: ['allMoney'];
        $dynArrs = self::staticsDynArr();
        $groupFields = ['bus_id'];
        $res = self::staticsMonthly($yearmonth, $moneyTypeN, 'bill_time', $groupFields, 'dataType', $dynArrs);
        $res['dynDataList']['bus_id'] = BusService::mainModel()->column('licencePlateSeats', 'id');
        //20230220:拼接座位数由大到小
        $con[] = ['owner_type', '=', 'self'];
        $busSeatsArr = BusService::mainModel()->where($con)->column('seats', 'id');
        foreach ($res['data'] as &$v) {
            $v['seats'] = Arrays::value($busSeatsArr, $v['bus_id']);
        }

        $res['data'] = Arrays2d::sort($res['data'], 'seats', 'desc');

        return $res;
    }

    public static function yearlyStatics($year, $moneyType) {
        $moneyTypeN = $moneyType ?: ['allMoney'];
        $dynArrs = self::staticsDynArr();
        $groupFields = ['bus_id'];
        $res = self::staticsYearly($year, $moneyTypeN, 'bill_time', $groupFields, 'dataType', $dynArrs);
        $res['dynDataList']['bus_id'] = BusService::mainModel()->column('licencePlateSeats', 'id');

        $con[] = ['owner_type', '=', 'self'];
        $busSeatsArr = BusService::mainModel()->where($con)->column('seats', 'id');
        foreach ($res['data'] as &$v) {
            $v['seats'] = Arrays::value($busSeatsArr, $v['bus_id']);
        }

        $res['data'] = Arrays2d::sort($res['data'], 'seats', 'desc');

        return $res;
    }


    public static function staticsBusByMonth($con = [], $orderBy = '') {
        return self::staticsBus('month', $con, $orderBy);
    }

    /**
     * 按年统计驾驶员信息
     * @param type $con
     * @return type
     */
    public static function staticsBusByYear($con = [], $orderBy = "") {
        return self::staticsBus('year', $con, $orderBy);
    }

    /**
     * 20220922:按车辆聚合查询
     * @param type $staticsBy
     * @param type $con
     * @param type $orderBy
     * @return type
     */
    protected static function staticsBus($staticsBy = 'date', $con = [], $orderBy = '') {
        //调用公共聚合查询逻辑
        return self::commStaticsTimeGroup($staticsBy, $con, function($con, $groupField, $orderByStr) {
                    $data = self::where($con)
                            ->group("company_id,bus_id,date_format( `time`, '" . $groupField . "' ) " . $orderByStr)
                            ->field("company_id,bus_id,
                                    date_format( `time`, '" . $groupField . "' ) as belongTime,
                                    count(*) as oilingCount")
                            ->select();
                    return $data ? $data->toArray() : [];
                }, $orderBy);
    }
    
    
    /**
     * 20221114：尝试将记录绑定到加油数据中
     */
    public function tryBind() {
        $info = $this->get();
        if (!$info) {
            throw new Exception('油卡记录不存在');
        }
        $has = BusOilingService::hasSource($info['id']);
        if ($has) {
            throw new Exception('油卡记录已关联');
        }

        $res = $this->doBind();
        if (!$res) {
            throw new Exception('没有关联的加油填报记录');
        }
        return true;
    }

    protected function doBind() {
        $info = $this->get();

        $con[] = ['number', '=', $info['volume']];
        $con[] = ['prize', '=', $info['prize']];
        $con[] = ['time', '>=', $info['bill_time']];
        $con[] = ['time', '<=', date('Y-m-d H:i:s', strtotime($info['bill_time']) + 86400)];
        $oilInfo = BusOilingService::where($con)->whereNull('source_id')->find();

        if ($oilInfo) {
            return BusOilingService::getInstance($oilInfo['id'])->update(['source_id' => $info['id'],'oil_by'=>'sinopec']);
        }
        return false;
    }

    /**
     * 20221115：尝试将记录添加到加油数据中
     */
    public function tryAdd() {
        if ($this->doBind()) {
            return true;
        }
        $info = $this->get();
        if (!$info) {
            throw new Exception('油卡记录不存在不能添加');
        }
        $has = BusOilingService::hasSource($info['number']);
        if ($has) {
            throw new Exception('油卡记录已关联不能添加');
        }
        // 提取加油记录
        return self::toBusOil($info);
    }

    
    
    /**
     * 
     */
    public static function toBusOil($data) {
        $sId = Arrays::value($data, 'id');
        if (!$sId) {
            return false;
        }
        $has = BusOilingService::hasSource($sId);
        if ($has) {
            return false;
        }
        $busId = Arrays::value($data, 'bus_id');
        if (!$busId) {
            throw new Exception('本条记录没有车辆信息');
        }
        // 20221115:GPS定位公里数
        $sData['gps_mile'] = GpsJt808PlaceService::getBusKilometer($busId, $data['endtime']);
        // 20221115:无法判断，全部默认满
        $sData['is_full'] = 1;
        // 加油机默认本站
        $sData['station_type'] = 1;
        $sData['bus_id'] = $busId;
        $sData['driver_id'] = '';
        $sData['prize']     = $data['prize'];
        $sData['number']    = $data['volume'];
        $sData['time']      = $data['bill_time'];
        $sData['source_id'] = $data['id'];
        // 20231205
        $sData['from_table']    = self::getTable();
        $sData['from_table_id'] = Arrays::value($data, 'id');
        // 加油渠道
        $sData['oil_by']        = 'sinopec';
        
        $res = BusOilingService::saveRam($sData);
        DbOperate::dealGlobal();
        return $res;
    }
}
