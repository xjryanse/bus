<?php

namespace xjryanse\bus\service\dragLog;

/**
 * 
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
     * 洗车时间
     */
    public function fDragTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 洗车费用
     */
    public function fDragMoney() {
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
    public function fFile() {
        return $this->getFFieldValue(__FUNCTION__);
    }

}
