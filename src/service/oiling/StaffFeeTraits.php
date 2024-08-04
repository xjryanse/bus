<?php
namespace xjryanse\bus\service\oiling;

use xjryanse\logic\DbOperate;
use xjryanse\logic\Arrays;
use xjryanse\finance\service\FinanceStaffFeeListService;
/**
 * 计算逻辑
 */
trait StaffFeeTraits{

    /**
     * 同步费用报销数据
     * 新增后，更新后，删除后
     */
    public function staffFeeSync(){
        $info   = $this->get();
        $payBy  = Arrays::value($info, 'pay_by');
        // 记录不存在，或不是现金报销的删除
        if(DbOperate::isGlobalDelete(self::getTable(), $this->uuid) 
                || !$info || $payBy != 'cash'){
            // 删除报销明细
            $fromTable      = self::getTable();
            $fromTableId    = $this->uuid;

            return FinanceStaffFeeListService::fromTableDataClear($fromTable, $fromTableId);
        }
        // 调用FinanceStaffFeeListService 的同步方法
        
        $fromTable      = self::getTable();
        $fromTableId    = $this->uuid;
        // TODO:加油固定用
        $feeType        = 'jiaYou';
        $money          = Arrays::value($info, 'prize');
        if(!intval($money)){
            return false;
        }
        $data = [];
        $data['bus_id']     = Arrays::value($info, 'bus_id');
        $data['user_id']    = Arrays::value($info, 'driver_id');
        $data['apply_time'] = Arrays::value($info, 'time');

        return FinanceStaffFeeListService::dataToStaffFee($fromTable, $fromTableId, $feeType, $money, $data);
    }
    
    
}
