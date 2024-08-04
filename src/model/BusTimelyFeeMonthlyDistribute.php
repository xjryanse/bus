<?php
namespace xjryanse\bus\model;

/**
 * 车辆时段费用分摊表
 */
class BusTimelyFeeMonthlyDistribute extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'bus_id',
            // 去除prefix的表名
            'uni_name'  =>'bus',
            'uni_field' =>'id',
            'in_list'   => false,            
            'del_check'=> true,
        ],
        [
            'field'     =>'timely_fee_id',
            // 去除prefix的表名
            'uni_name'  =>'bus_timely_fee',
            'uni_field' =>'id',
        ],
        [
            'field'     =>'dept_id',
            // 去除prefix的表名
            'uni_name'  =>'w_system_company_dept',
            'uni_field' =>'id',
        ],
    ];

    // 20231019:默认的时间字段，每表最多一个
    // public static $timeField = 'end_time';
}