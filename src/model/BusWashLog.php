<?php
namespace xjryanse\bus\model;

/**
 * 
 */
class BusWashLog extends Base
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
    
    public static $multiPicFields = ['evidence'];
    /**
     * 2023-10-10多图
     * @param type $value
     * @return type
     */
    public function getEvidenceAttr($value) {
        return self::getImgVal($value, true);
    }

    public function setEvidenceAttr($value) {
        return self::setImgVal($value);
    }

}