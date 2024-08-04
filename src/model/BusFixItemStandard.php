<?php

namespace xjryanse\bus\model;

use think\Db;

/**
 * 车辆维修项目标准报价
 */
class BusFixItemStandard extends Base {

    use \xjryanse\traits\ModelUniTrait;

    // 20230516:数据表关联字段
    public static $uniFields = [
//        [
//            'field' => 'fix_id',
//            // 去除prefix的表名
//            'uni_name' => 'bus_fix',
//            'uni_field' => 'id',
//            'del_check' => true,
//        ],
    ];

    /**
     * 20230807：反置属性
     * @var type
     */
    public static $uniRevFields = [
//        [
//            'table'     =>'finance_statement_order',
//            'field'     =>'belong_table_id',
//            'uni_field' =>'id',
//            'exist_field'   =>'isStatementOrderExist',
//            'condition'     =>[
//                // 关联表，即本表
//                'belong_table'=>'{$uniTable}'
//            ]
//        ]
    ];

    // public static $picFields = ['adm_file_id','mile_pic'];
}
