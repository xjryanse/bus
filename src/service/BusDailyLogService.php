<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;

/**
 * 
 */
class BusDailyLogService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;


    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusDailyLog';

    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        
    }

    /**
     * 钩子-更新前
     */
    public static function ramPreUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-更新后
     */
    public static function ramAfterUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        
    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete() {
        
    }

    /**
     *
     */
    public function fId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 公司id
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
     * 材料已归档
     */
    public function fHasLog() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 实名制
     */
    public function fPassRealname() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 包车合同
     */
    public function fBaoContract() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 行车路单
     */
    public function fRouteBill() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 趟检单
     */
    public function fTangPic() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 日期
     */
    public function fBelongDate() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fSourceId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 备注
     */
    public function fRemark() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fStatus() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fHasUsed() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fIsLock() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fIsDelete() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fCreater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fUpdater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fCreateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fUpdateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

}
