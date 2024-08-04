<?php
namespace xjryanse\bus\model;

/**
 * 20240615:拖车记录
 */
class BusDragLog extends Base
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
    
    public static $multiPicFields = ['file'];
    /**
     * 2023-10-10多图
     * @param type $value
     * @return type
     */
    public function getFileAttr($value) {
        return self::getImgVal($value, true);
    }

    public function setFileAttr($value) {
        return self::setImgVal($value);
    }

}