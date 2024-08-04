<?php
namespace xjryanse\bus\service\oiling;

use xjryanse\logic\Arrays;
use think\Db;
/**
 * 计算逻辑
 */
trait PaginateTraits{
    
    
    /**
     * 分页的查询
     * @param type $con
     * @param type $order
     * @param type $perPage
     * @return type
     */
    public static function paginateForFullStatics($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false) {
        $sql    = self::mainModel()->sqlFullStatics($con);
        $table  = $sql.' as aaa';
        $res    = Db::table($table)->order('end_time desc')->paginate($perPage);
        
        $resArr = $res ? $res->toArray() : [];

        $fieldSum = [];
        $fieldSum[]   = 'sum(prize) as prize';
        $fieldSum[]   = 'sum(number) as number';
        $fieldSum[]   = 'count(1) as times';
        
        $sum = self::mainModel()
                ->where($con)
                ->field(implode(',',$fieldSum))
                ->find();
        $resArr['withSum'] = 1;
        $resArr['sumData'] = $sum;
        return $resArr;
    }
}
