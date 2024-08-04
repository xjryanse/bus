<?php
namespace xjryanse\bus\service\mile;

/**
 * 分页复用列表
 */
trait FieldTraits{

    /**
     * 车辆id
     */
    public function fBusId() {
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
     * gps公里数偏差值
     */
    public function fGpsDiff() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * GPS提取的公里数
     */
    public function fGpsMile() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 手工申报公里数偏差
     */
    public function fHandleDiff() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 手工申报公里数
     */
    public function fHandleMile() {
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
     * 备注
     */
    public function fRemark() {
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

    /**
     * 申报时间
     */
    public function fUplTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fFromTable() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fFromTableId() {
        return $this->getFFieldValue(__FUNCTION__);
    }
}
