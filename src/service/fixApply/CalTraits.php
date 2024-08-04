<?php
namespace xjryanse\bus\service\fixApply;

use xjryanse\system\service\SystemCondService;
/**
 * 
 */
trait CalTraits{

    /**
     * 20240801：计算是否需要审批
     */
    public function calNeedAppr(){
        // 计算维修是否需审批
        $operateKey = 'isBusFixApplyNeedAppr';
        // 20240415：加入了未达成判断抛出异常中断流程
        $dataId = $this->uuid;
        $info   = $this->get();
        return SystemCondService::isReachByItemKey('busFixApply', $operateKey, $dataId, $info);
    }

    /**
     * 20220623:计算佣金总额
     */
    public function calFeeMoney() {
        $lists = $this->objAttrsList('busFixApplyItem');
        return array_sum(array_column($lists, 'total_prize'));
    }
}
