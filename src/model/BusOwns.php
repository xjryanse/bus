<?php
namespace xjryanse\bus\model;

/**
 * 车辆持股表
 */
class BusOwns extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'bus_id',
            'uni_name'  =>'bus',
        ],
    ];

}