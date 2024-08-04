<?php
namespace xjryanse\bus\model;

/**
 * 车辆高速费
 */
class BusHighwayFee extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'bus_id',
            'uni_name'  =>'bus',
            'in_list'   => false,
        ],
        [
            'field'     =>'order_id',
            // 去除prefix的表名
            'uni_name'  =>'order',
            'uni_field' =>'id',
            'del_check'=> true,
        ],
        [
            'field'     =>'bao_bus_id',
            // 去除prefix的表名
            'uni_name'  =>'order_bao_bus',
            'uni_field' =>'id',
            'del_check' => true,
            'del_msg'   => '已有{$count}条高速记录，请先删除才能操作'
        ]
    ];

    // 20231019:默认的时间字段，每表最多一个
    public static $timeField = 'end_time';
}