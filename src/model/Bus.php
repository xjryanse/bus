<?php
namespace xjryanse\bus\model;

use think\Db;
/**
 * 
 */
class Bus extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'customer_id',
            'uni_name'  =>'customer',
            'uni_field' =>'id',
            'in_list'   => false,
            'in_statics'=> true,
            'in_exist'  => true,
            'del_check' => true,
        ],
    ];
    
        /**
     * 20230807：反置属性
     * @var type
     */
    public static $uniRevFields = [
        [
            'table'     =>'wechat_we_pub_qr_scene',
            'field'     =>'from_table_id',
            'uni_field' =>'id',
            'exist_field'   =>'isWechatWePubQrSceneExist',
            'condition'     =>[
                // 关联表，即本表
                'from_table'=>'{$uniTable}'
            ]
        ]
    ];
    
    
    public static $picFields = ['bus_pic'];
    
    public static $multiPicFields = ['discard_zxzm','discard_yszjxzm','discard_jdchszm'];
    
    public function getDiscardJdchszmAttr($value) {
        return self::getImgVal($value, true);
    }
    
    public function setDiscardJdchszmAttr($value) {
        return self::setImgVal($value);
    } 
    
    public function getDiscardYszjxzmAttr($value) {
        return self::getImgVal($value, true);
    }
    
    public function setDiscardYszjxzmAttr($value) {
        return self::setImgVal($value);
    }
    /**
     * 用户头像图标
     * @param type $value
     * @return type
     */
    public function getBusPicAttr($value) {
        return self::getImgVal($value);
    }

    /**
     * 图片修改器，图片带id只取id
     * @param type $value
     * @throws \Exception
     */
    public function setBusPicAttr($value) {
        return self::setImgVal($value);
    }
    
    
    public function getDiscardZxzmAttr($value) {
        return self::getImgVal($value, true);
    }
    
    public function setDiscardZxzmAttr($value) {
        return self::setImgVal($value);
    }
    
//    /**
//     * 20231221：费用报销，维修科目
//     * 
//     * 包车系统费用报销
//     * @param type $con
//     * @return type
//     */
//    public static function sqlBusWithCircuitPlate($con = []){
//        $table   = self::getTable();
//
//        $field   = [];
//        $field[] = 'a.id';
//        $field[] = 'a.dept_id';
//        $field[] = 'a.bus_type';
//        $field[] = 'a.bus_cate';
//        $field[] = 'a.tech_cate';
//        $field[] = 'a.owner_type';
//        // 车辆品牌
//        $field[] = 'a.bus_fuel';
//        $field[] = 'a.bus_brand';
//        $field[] = 'a.bus_model';
//        // 车辆状态        
//        $field[] = 'a.status';
//        $field[] = 'a.seats';
//        $field[] = 'a.sort';
//        
//        $field[] = 'a.licence_plate';
//        $field[] = 'b.brand_no';
//        $field[] = 'b.circuit_name';
//        $field[] = 'b.circuit_type';
//        $field[] = 'b.miles';
//        $field[] = 'b.times';
//        $field[] = 'b.from_station_name';
//        $field[] = 'b.to_station_name';
//        $field[] = 'b.main_pass_station';
//        $field[] = 'b.main_stop_station';
//        // 线路牌开始时间
//        $field[] = 'b.start_time as lineCertTime';
//        // 线路牌结束时间
//        $field[] = 'b.end_time as lineLimitTime';
//
//        $circuitPlateTable = 'w_circuit_plate';
//        
//        $sql = Db::table($table)->alias('a')
//                ->leftJoin($circuitPlateTable.' b','a.id = b.bus_id')
//                ->where($con)
//                ->field(implode(',',$field))
//                ->buildSql();
//        
//        
//        return $sql;
//    }

    /**
     * 车辆证件sql
     */
    public static function busCertSql(){
        // transport:运输证；vehicle:行驶证；许可证：permit;carriers:承运险；commer:商业险；insure:强制险
        $certKeys = ['transport','vehicle','permit','carriers','commer','insure'];

//                    `aa`.`id` AS `id`,
//            `aa`.`company_id` AS `company_id`,
//            `aa`.`dept_id` AS `dept_id`,

        $sql = "(SELECT
            `aa`.`id` AS `bus_id`,
            `aa`.`passenger_max` AS `passenger_max`,";
        foreach($certKeys as $k){
            //             max( ( CASE `bb`.`cert_key` WHEN 'transport' THEN `bb`.`certStatus` ELSE NULL END ) ) AS `transportCertStatus`,
            $sql .= "max( ( CASE `bb`.`cert_key` WHEN '".$k."' THEN date_format( `bb`.`cert_limit_time`, '%Y-%m-%d' ) ELSE NULL END ) ) AS `".$k."LimitTime`,
                max( ( CASE `bb`.`cert_key` WHEN '".$k."' THEN `bb`.`cert_time` ELSE NULL END ) ) AS `".$k."CertTime`,
                max( ( CASE `bb`.`cert_key` WHEN '".$k."' THEN `bb`.`certStatus` ELSE NULL END ) ) AS `".$k."CertStatus`,
                max( ( CASE `bb`.`cert_key` WHEN '".$k."' THEN `bb`.`cert_no` ELSE NULL END ) ) AS `".$k."CertNo`,";
        }

        $sql .= "1 AS `t` FROM ( `w_bus` `aa` LEFT JOIN `w_view_cert_bus` `bb` ON ( ( `aa`.`id` = `bb`.`bus_id` ) ) )  WHERE ( `aa`.`owner_type` = 'self' )  GROUP BY `aa`.`id`)";
        
        return $sql;
    }

}