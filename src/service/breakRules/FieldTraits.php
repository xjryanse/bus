<?php
namespace xjryanse\bus\service\breakRules;

/**
 * 触发复用
 */
trait FieldTraits{

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
     * [冗]车牌号码
     */
    public function fLicencePlate() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 当班司机
     */
    public function fDriverId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * [冗]当班司机姓名
     */
    public function fDriverName() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 违章时间
     */
    public function fBreakTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 违章地点
     */
    public function fBreakPlace() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 违章原因
     */
    public function fBreakReason() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 罚款金额
     */
    public function fPunishMoney() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 扣分
     */
    public function fPunishScore() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 状态:0未处理，1已处理
     */
    public function fDealStatus() {
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
