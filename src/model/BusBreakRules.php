<?php
namespace xjryanse\bus\model;

/**
 * 车辆违章
 */
class BusBreakRules extends Base
{
    use \xjryanse\traits\ModelUniTrait;

    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field' => 'bus_id',
            'uni_name' => 'bus',
            'in_list'   => false,            
        ],
        [
            'field'     => 'batch_id',
            'uni_name'  => 'bus_break_rules_batch',
            'in_list'   => false,
        ],
    ];

}