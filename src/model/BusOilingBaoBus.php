<?php
namespace xjryanse\bus\model;

/**
 * 车辆加油记录-出车趟次关联表
 * 多对多
 */
class BusOilingBaoBus extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'bus_oiling_id',
            // 去除prefix的表名
            'uni_name'  =>'bus_oiling',
            'uni_field' =>'id',
            'del_check' => true,
        ],
        [
            'field'     =>'bao_bus_id',
            // 去除prefix的表名
            'uni_name'  =>'order_bao_bus',
            'uni_field' =>'id',
            'del_check' => true,
        ]
    ];
}