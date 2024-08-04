<?php

namespace xjryanse\bus\model;

/**
 * 车辆事故
 */
class BusAccident extends Base {

    use \xjryanse\traits\ModelUniTrait;

    // 20230516:数据表关联字段
    public static $uniFields = [
        [
            'field' => 'bus_id',
            'uni_name' => 'bus',
            'in_list'   => false,            
        ],
    ];
    // public static $picFields = ['acc_xieyi_img'];
    // 改造成多图？
    public static $multiPicFields = ['acc_xieyi_img'];

    /**
     * 车公里表照片
     * @param type $value
     * @return type
     */
    public function getAccXieyiImgAttr($value) {
        return self::getImgVal($value, true);
    }

    public function setAccXieyiImgAttr($value) {
        return self::setImgVal($value);
    }
}
