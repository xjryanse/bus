<?php
namespace xjryanse\bus\model;

use think\Db;
/**
 * 
 */
class BusOiling extends Base
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
            'field'     =>'last_full_id',
            // 去除prefix的表名
            'uni_name'  =>'bus_oiling',
            'uni_field' =>'id',
        ]
    ];
    
        
    /**
     * 20230807：反置属性
     * @var type
     */
    public static $uniRevFields = [
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
    
    public static $picFields = ['mile_pic','equip_pic'];
    
    // 20231019:默认的时间字段，每表最多一个
    public static $timeField = 'time';
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
     * 加油机照片
     * @param type $value
     * @return type
     */
    public function getEquipPicAttr($value) {
        return self::getImgVal($value);
    }
    public function setEquipPicAttr($value) {
        return self::setImgVal($value);
    }
    
    public function sqlFullStatics($con){
        $fields     = [];
        $fields[]   = 'max(time) as end_time';
        $fields[]   = 'min(last_time) as start_time';

        $fields[]   = 'ifnull(max(kilometer),0) as kilometer';
        $fields[]   = 'ifnull(min(last_kilometer),0) as last_kilometer';
        $kiloDiffStr = 'ifnull(max(kilometer),0) - ifnull(min(last_kilometer),0)';
        $fields[]   = $kiloDiffStr.' as kiloDiff';

        $fields[]   = 'ifnull(max(gps_mile),0) as gps_mile';
        $fields[]   = 'ifnull(min(last_gps_mile),0) as last_gps_mile';
        $gpsMillDiffStr = 'ifnull(max(gps_mile),0) - ifnull(min(last_gps_mile),0)';
        $fields[]   = $gpsMillDiffStr.' as gpsMileDiff';

        $fields[]   = 'sum(prize) as prize';
        $fields[]   = 'sum(number) as number';
        $fields[]   = 'count(1) as times';
        // 百公里油钱(GPS)
        $fields[]   = 'sum(prize)/('.$gpsMillDiffStr.') * 100 as gPMP';
        // 百公里油耗(GPS)
        $fields[]   = 'sum(number)/('.$gpsMillDiffStr.') * 100 as gPNP';
        // 百公里油钱(表)
        $fields[]   = 'sum(prize)/('.$kiloDiffStr.') * 100 as kPMP';
        // 百公里油耗(表)
        $fields[]   = 'sum(number)/('.$kiloDiffStr.') * 100 as kPNP';
        // 状态是1，控制前端显示
        $fields[]   = '1 as status';

        $groups     = ['bus_id','last_full_id'];

        $fieldsN    = array_merge($fields, $groups);

        $sql = self::where($con)
                ->field(implode(',',$fieldsN))
                ->group(implode(',',$groups))
                ->buildSql();
        return $sql;
    }
    
    
    /**
     * 20231221：费用报销，加油科目
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
        $field[] = 'time as apply_time';
        $field[] = 'prize as money';
        // 报销人传，多张
        $field[] = 'concat(mile_pic,",",equip_pic) as file';
        // 后台传，1账
        $field[] = 'adm_file_id as annex';
        
        $field[] = 'has_settle';
        $field[] = "'".$table."' as sourceTable";
        $field[] = 'status';
        $field[] = "'oil' as feeCate";
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