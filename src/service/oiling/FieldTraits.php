<?php
namespace xjryanse\bus\service\oiling;

/**
 * 分页复用列表
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
     * 卡号
     */
    public function fCard() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 持卡人
     */
    public function fCardholder() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 交易时间
     */
    public function fTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 交易类型(1加油,2圈存）
     */
    public function fType() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fKilometer() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fGpsMile() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    /**
     * 金额
     */
    public function fPrize() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 油品
     */
    public function fOils() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 数量
     */
    public function fNumber() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fBaoBusId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 单价
     */
    public function fUnitPrice() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 奖励积分
     */
    public function fIntegral() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 余额
     */
    public function fBalance() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 地址
     */
    public function fAddress() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 排序
     */
    public function fSort() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fLastId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fLastFullId() {
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
