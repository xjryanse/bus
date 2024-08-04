<?php
namespace xjryanse\bus\model;

/**
 * 车辆违章累积多次批量处理
 */
class BusBreakRulesBatch extends Base
{
    use \xjryanse\traits\ModelUniTrait;

    // 20230516:数据表关联字段
    public static $uniFields = [
//        [
//            'field' => 'bus_id',
//            'uni_name' => 'bus',
//            'in_list'   => false,            
//        ],
    ];

}