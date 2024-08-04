<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use app\bus\service\ViewBusCardEtcService;
use xjryanse\bus\service\BusService;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;
use xjryanse\logic\Debug;
use Exception;

/**
 * 车辆高速费
 */
class BusHighwayFeeService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusHighwayFee';
    // 20231204批量保存时覆盖
    protected static $isSaveAllCover = true;

    use \xjryanse\bus\service\highwayFee\TriggerTraits;
    use \xjryanse\bus\service\highwayFee\DoTraits;
    
    /**
     * 额外详情
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
            return $lists;
        }, true);
    }
    
    /**
     * 钩子-保存前
     */
    public static function extraPreSave(&$data, $uuid) {
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
        
        // Debug::dump($data);
        return $data;
    }

    /*     * *** 2023-01-17数据统计 ***************************** */

    protected static function staticsFields() {
        $fields[] = "bus_id";
        // 趟数
        $fields[] = "count(1) AS `tangCount`";
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
        $res = self::staticsMonthly($yearmonth, $moneyTypeN, 'end_time', $groupFields, 'dataType', $dynArrs);
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
        $res = self::staticsYearly($year, $moneyTypeN, 'end_time', $groupFields, 'dataType', $dynArrs);
        $res['dynDataList']['bus_id'] = BusService::mainModel()->column('licencePlateSeats', 'id');

        $con[] = ['owner_type', '=', 'self'];
        $busSeatsArr = BusService::mainModel()->where($con)->column('seats', 'id');
        foreach ($res['data'] as &$v) {
            $v['seats'] = Arrays::value($busSeatsArr, $v['bus_id']);
        }

        $res['data'] = Arrays2d::sort($res['data'], 'seats', 'desc');

        return $res;
    }

    /**
     * 钩子-保存后
     */
    public static function extraAfterSave(&$data, $uuid) {
        
    }

    /**
     * 钩子-更新前
     */
    public static function extraPreUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-更新后
     */
    public static function extraAfterUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-删除前
     */
    public function extraPreDelete() {
        
    }

    /**
     * 钩子-删除后
     */
    public function extraAfterDelete() {
        
    }

    /**
     * 车辆id
     */
    public function fBusId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 卡号
     */
    public function fCard() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fCompanyId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 创建时间
     */
    public function fCreateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 创建者，user表
     */
    public function fCreater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 通行日期
     */
    public function fDate() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 出口时间
     */
    public function fEndTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 收费类型
     */
    public function fFeeType() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 有使用(0否,1是)
     */
    public function fHasUsed() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 锁定（0：未删，1：已删）
     */
    public function fIsDelete() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 锁定（0：未锁，1：已锁）
     */
    public function fIsLock() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 已收金额
     */
    public function fPayPrize() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 应收金额
     */
    public function fPrize() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 省份
     */
    public function fProvince() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 退款金额
     */
    public function fRefundPrize() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 备注
     */
    public function fRemark() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 收费信息a
     */
    public function fRemarksA() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 收费信息b
     */
    public function fRemarksB() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 通行区间
     */
    public function fSection() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 结算日期
     */
    public function fSettlementDate() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 车情
     */
    public function fSituation() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 排序
     */
    public function fSort() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 入口时间
     */
    public function fStartTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 状态(0禁用,1启用)
     */
    public function fStatus() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 更新时间
     */
    public function fUpdateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 更新者，user表
     */
    public function fUpdater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

}
