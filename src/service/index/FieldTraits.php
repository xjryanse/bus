<?php
namespace xjryanse\bus\service\index;

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
     * 20231227：部门id
     * @return type
     */
    public function fDeptId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fAppId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fBusType() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    /**
     *
     */
    public function fCompanyId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fCustomerId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fUserId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 文章id
     */
    public function fArticleId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 浏览用户id
     */
    public function fScanUserId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /*     * 当前司机* */
    /**
     * 字段逐步废弃，使用driver_ability表替代
     * 20240403
     * @return type
     */
    public function fCurrentDriver() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 浏览用户微信openid
     */
    public function fScanOpenId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 排序
     */
    public function fSort() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fSeats() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 状态(0禁用,1启用)
     */
    public function fStatus() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fEquipment() {
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

    public function fLicencePlate() {
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

    public function fMngPrize() {
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
