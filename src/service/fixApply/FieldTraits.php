<?php
namespace xjryanse\bus\service\fixApply;

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

    public function fDeptId() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fBusId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fFixCustomerId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fFixUserId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    public function fApplyTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fApprovalThingId() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
}
