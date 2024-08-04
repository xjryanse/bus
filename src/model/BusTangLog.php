<?php
namespace xjryanse\bus\model;

/**
 * 车辆日趟检记录表（旅游车不需要每趟都检，有出车当日有检即可）
 */
class BusTangLog extends Base
{
    use \xjryanse\traits\ModelUniTrait;
    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field'     =>'bus_id',
            'uni_name'  =>'bus',
            'in_list'   => false,            
        ],
    ];
    
    public static $picFields = ['tang_pic'];

    public function getTangPicAttr($value) {
        return self::getImgVal($value);
    }

    public function setTangPicAttr($value) {
        return self::setImgVal($value);
    }    

}