<?php
namespace xjryanse\bus\model;

/**
 * 车辆时段付费
 */
class BusTimelyFee extends Base
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
        ]
    ];

    // 20231019:默认的时间字段，每表最多一个
    // public static $timeField = 'end_time';
}