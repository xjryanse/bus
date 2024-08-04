<?php
namespace xjryanse\bus\service\washLog;
/**
 * 用户维度过滤数据
 */
trait MeTraits{

    /**
     * 20231128:用于提取当前驾驶员上报的记录
     * @param type $data
     * @param type $uuid
     */
    public static function paginateDriverMe($con = [], $order = '', $perPage = 10, $having = '', $field = "*", $withSum = false) {
        $con[] = ['driver_id','=',session(SESSION_USER_ID)];
        
        $res = self::paginateX($con, $order, $perPage, $having, $field, $withSum);

        return $res;
    }
    
    
}
