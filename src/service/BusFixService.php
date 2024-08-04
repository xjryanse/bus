<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\finance\interfaces\StaffFeeOutInterface;
/**
 * 车辆维修
 */
class BusFixService extends Base implements MainModelInterface, StaffFeeOutInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    use \xjryanse\traits\FinanceSourceModelTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusFix';

    use \xjryanse\bus\service\fix\FieldTraits;
    use \xjryanse\bus\service\fix\TriggerTraits;
    use \xjryanse\bus\service\fix\ListTraits;
    use \xjryanse\bus\service\fix\DoTraits;
    use \xjryanse\bus\service\fix\PaginateTraits;
    use \xjryanse\bus\service\fix\StaffFeeTraits;

    /**
     * 额外详情
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    foreach ($lists as &$v) {
                        // 是否本人的报销单
                        $v['driverIsMe'] = $v['driver_id'] == session(SESSION_USER_ID) ? 1:0;
                    }

                    return $lists;
                }, true);
    }
    
    /**
     * 20231216:订单账单添加
     */
    public function addStatementOrder() {
        $info = $this->get();
        // 20231228
        if(!$info['driver_id']){
            return false;
        }
        
        $prizeKey   = $info['pay_by'] == 'cash' ? 'staffFee' : 'customerBill';
        $prizeField = 'prize';

        $reflectKeys = ['driver_id'=>'user_id'];

        return $this->financeCommAddStatementOrder($prizeKey, $prizeField, $reflectKeys);
    }
    
    public function updateStatementOrder() {
        // 如果款项信息有变
        // 删除之前的账单
        // 再新增账单
        if($this->updateDiffsHasField(['driver_id','prize','pay_by'])){
            // 先清理
            $this->financeCommClearStatementOrder();
            // 再添加
            $this->addStatementOrder();
        }
    }

    public function feeMoneyUpdateRam() {
        $data['prize'] = $this->calFeeMoney();
        return $this->doUpdateRam($data);
    }

    /**
     * 20220623:计算佣金总额
     */
    public function calFeeMoney() {
        $lists = $this->objAttrsList('busFixItem');
        return array_sum(array_column($lists, 'total_prize'));
    }
}
