<?php
namespace xjryanse\bus\model;

/**
 * 车辆驾驶员能力
 */
class BusDriverAbility extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段1
    public static $uniFields = [
        [
            'field'     =>'bus_id',
            'uni_name'  =>'bus',
        ],        
        [
            'field'     =>'driver_id',
            'uni_name'  =>'user',
            'uni_field' =>'id',
            'in_list'   => false,
            'in_statics'=> false,
            'in_exist'  => true,
            'del_check' => false,
        ]
    ];
    /**
     * 司机车牌
     * @return type
     */
    /*
    public static function driverLicencePlateSql(){
        $con        = [];
        $fields     = [];
        $fields[]   = 'a.driver_id';
        $fields[]   = 'group_concat(b.licence_plate) as licencePlates';
        $fields[]   = 'group_concat( DISTINCT ( b.seats ) ) AS busSeats';
        $fields[]   = 'group_concat( DISTINCT ( c.circuit_name ) ) AS circuitName';

        $sql    = self::where($con)
                ->field(implode(',',$fields))
                ->alias('a')
                ->join('w_bus b','a.bus_id = b.id')
                ->join('w_circuit_plate c','b.id = c.bus_id')
                ->group('a.driver_id')
                ->buildSql();
        return $sql;
    }
    */
    
}