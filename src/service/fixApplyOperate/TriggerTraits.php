<?php

namespace xjryanse\bus\service\fixApplyOperate;

use xjryanse\logic\DataCheck;
use xjryanse\logic\Arrays;
use xjryanse\bus\service\BusFixApplyService;
use Exception;

/**
 * 字段复用列表
 */
trait TriggerTraits{
    /**
     * 钩子-保存前
     */
    public static function extraPreSave(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }

    public static function extraPreUpdate(&$data, $uuid) {
        self::stopUse(__METHOD__);
    }
    
    public function extraPreDelete() {
        self::stopUse(__METHOD__);
    }

    /**
     * 20230923:批量保存前处理
     * @param type $data
     * @param type $uuid
     */
    public static function ramPreSaveAll(&$data) {

    }

    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        $keys = ['apply_id','operate_type'];

        DataCheck::must($data, $keys);
        // 如果是AApply，且单据的 need_appr 是1；且audit_status 不是1；报错
        if(Arrays::value($data, 'operate_type') == 'AApply'){
            $applyInfo = BusFixApplyService::getInstance($data['apply_id'])->get();
            if($applyInfo['need_appr'] && $applyInfo['audit_status'] != 1){
                throw new Exception('您已设置该单据需要审批，但目前流程尚未通过不可操作');
            }
        }
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        $applyId = Arrays::value($data, 'apply_id');
        // 20240802:计算需审批且没有审批单，写一条
        if(BusFixApplyService::getInstance($applyId)->calNeedAppr() 
                && !BusFixApplyService::getInstance($applyId)->fApprovalThingId()){
            BusFixApplyService::getInstance($applyId)->approvalAdd();
        }
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
    public function ramAfterDelete($rawData) {

    }

}
