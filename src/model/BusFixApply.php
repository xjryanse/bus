<?php
namespace xjryanse\bus\model;

use think\Db;
/**
 * 车辆维修
 */
class BusFixApply extends Base
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
            'field'     =>'approval_thing_id',
            'uni_name'  =>'approval_thing',
            'in_list'   => false,            
        ],

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
    
    public static $multiPicFields = ['pictures', 'file_id'];

    /**
     * 2023-10-10多图
     * @param type $value
     * @return type
     */
    public function getPicturesAttr($value) {
        return self::getImgVal($value, true);
    }

    public function setPicturesAttr($value) {
        return self::setImgVal($value);
    }

    public function getFileIdAttr($value) {
        return self::getImgVal($value, true);
    }

    public function setFileIdAttr($value) {
        return self::setImgVal($value);
    }
    
    // 20231019:默认的时间字段，每表最多一个
    public static $timeField = 'apply_time';

}