<?php
namespace xjryanse\bus\model;

use think\Db;
/**
 * 车辆维修申请，项目明细
 */
class BusFixApplyItem extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'apply_id',
            // 去除prefix的表名
            'uni_name'  =>'bus_fix_apply',
            'uni_field' =>'id',
            'del_check'=> true,
        ],
        [
            'field'     =>'item_standard_id',
            // 去除prefix的表名
            'uni_name'  =>'bus_fix_item_standard',
            'uni_field' =>'id',
        ],
//        [
//            'field'     =>'bao_bus_id',
//            // 去除prefix的表名
//            'uni_name'  =>'order_bao_bus',
//            'uni_field' =>'id',
//            'del_check' => true,
//            'del_msg'   => '已有{$count}条加油记录，请先删除才能操作'
//        ]
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
    
    public static $multiPicFields = ['break_img','fix_img'];

    public function getBreakImgAttr($value) {
        return self::getImgVal($value, true);
    }

    public function setBreakImgAttr($value) {
        return self::setImgVal($value);
    }

    public function getFixImgAttr($value) {
        return self::getImgVal($value, true);
    }

    public function setFixImgAttr($value) {
        return self::setImgVal($value);
    }

}