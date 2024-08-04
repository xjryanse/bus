<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;

/**
 * 
 */
class BusAccidentService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\MainStaticsTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusAccident';

    use \xjryanse\bus\service\accident\TriggerTraits;

    
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function($lists) use ($ids) {

            foreach ($lists as &$v) {
                // 20240627:增加intval
                $v['hasPunishMoney'] = intval($v['punish_money']) ? 1 : 0; 
            }

            return $lists;
        },true);
    }
    /**
     *
     */
    public function fId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fCompanyId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 车辆id
     */
    public function fBusId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 洗车里程
     */
    public function fBusMile() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 洗车时间
     */
    public function fWashTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 洗车费用
     */
    public function fWashMoney() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 经度,纬度
     */
    public function fWashStationLocation() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 加油站名称
     */
    public function fWashStation() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 司机d
     */
    public function fDriverId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 上传凭据图片，逗号分隔
     */
    public function fEvidence() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 20220705
     */
    public function fHasWash() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 20220705
     */
    public function fBelongDate() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 排序
     */
    public function fSort() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 状态(0禁用,1启用)
     */
    public function fStatus() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 有使用(0否,1是)
     */
    public function fHasUsed() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 锁定（0：未锁，1：已锁）
     */
    public function fIsLock() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 锁定（0：未删，1：已删）
     */
    public function fIsDelete() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 备注
     */
    public function fRemark() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 创建者，user表
     */
    public function fCreater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 更新者，user表
     */
    public function fUpdater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 创建时间
     */
    public function fCreateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 更新时间
     */
    public function fUpdateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

}
