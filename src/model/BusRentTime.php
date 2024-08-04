<?php
namespace xjryanse\bus\model;

/**
 * 车辆租赁表
 */
class BusRentTime extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'bus_id',
            'uni_name'  =>'bus',
            'in_list'   => false,            
        ],
    ];

}