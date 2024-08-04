<?php
namespace xjryanse\bus\service\fixApplyItem;

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

    public function fApplyId() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
    public function fItemName() {
        return $this->getFFieldValue(__FUNCTION__);
    }
    
}
