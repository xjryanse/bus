<?php
namespace xjryanse\bus\model;

use think\Db;
/**
 * 车辆维修
 */
class BusFix extends Base
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
            'del_msg'   => '已有{$count}条加油记录，请先删除才能操作'
        ],
        [
            'field'     =>'apply_id',
            // 去除prefix的表名
            'uni_name'  =>'bus_fix_apply',
            'uni_field' =>'id',
            'del_check'=> true,
        ],
    ];
    
    /**
     * 20230807：反置属性
     * @var type
     */
    public static $uniRevFields = [
        [
            'table'     =>'finance_statement_order',
            'field'     =>'belong_table_id',
            'uni_field' =>'id',
            'exist_field'   =>'isStatementOrderExist',
            'condition'     =>[
                // 关联表，即本表
                'belong_table'=>'{$uniTable}'
            ]
        ],
        [
            'table'     =>'finance_staff_fee_list',
            'field'     =>'from_table_id',
            'uni_field' =>'id',
            'exist_field'   =>'isFinanceStaffFeeListExist',
            'condition'     =>[
                // 关联表，即本表
                'belong_table'=>'{$uniTable}'
            ]
        ]
    ];
    
    public static $picFields = ['adm_file_id','mile_pic'];
    
    public static $multiPicFields = ['file_id'];

        /**
     * 车公里表照片
     * @param type $value
     * @return type
     */
    public function getMilePicAttr($value) {
        return self::getImgVal($value);
    }

    public function setMilePicAttr($value) {
        return self::setImgVal($value);
    }
    
    /**
     * 附件
     * @param type $value
     * @return type
     */
    public function getAdmFileIdAttr($value) {
        return self::getImgVal($value);
    }

    /**
     * 图片修改器，图片带id只取id
     * @param type $value
     * @throws \Exception
     */
    public function setAdmFileIdAttr($value) {
        return self::setImgVal($value);
    }
    
    /**
     * 2023-10-10多图
     * @param type $value
     * @return type
     */
    public function getFileIdAttr($value) {
        return self::getImgVal($value, true);
    }
    
    public function setFileIdAttr($value) {
        return self::setImgVal($value);
    }
    
    /**
     * 20231221：费用报销，维修科目
     * 
     * 包车系统费用报销
     * @param type $con
     * @return type
     */
    public static function sqlBaoFinanceStaffFee($con = []){
        $table   = self::getTable();
        $field = [];
        $field[] = 'id';
        $field[] = 'order_id';
        $field[] = 'bao_bus_id';
        $field[] = 'payer_id as user_id';
        $field[] = 'bus_id';
        $field[] = 'fix_time as apply_time';
        $field[] = 'prize as money';
        // 报销人传，多张
        $field[] = 'file_id as file';
        // 后台传，1账
        $field[] = 'adm_file_id as annex';
        $field[] = 'has_settle';
        $field[] = "'".$table."' as sourceTable";
        $field[] = 'status';        
        $field[] = "'fix' as feeCate";
        $field[] = 'creater';
        $field[] = 'create_time';
        $field[] = '0 as financeStaffFeeListCount';

        // 20231231:只提现金报销部分
        $con[]=['pay_by','=','cash'];
        
        $sql = Db::table($table)
                ->where($con)
                ->field(implode(',',$field))
                ->buildSql();
        return $sql;
    }
}