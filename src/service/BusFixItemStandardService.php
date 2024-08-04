<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
use xjryanse\logic\Arrays2d;
use xjryanse\contract\service\ContractService;
/**
 * 车辆维修申请
 */
class BusFixItemStandardService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticsModelTrait;
    use \xjryanse\traits\ObjectAttrTrait;
    use \xjryanse\traits\FinanceSourceModelTrait;
    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusFixItemStandard';

//    use \xjryanse\bus\service\fixItem\FieldTraits;
//    use \xjryanse\bus\service\fixItem\TriggerTraits;
//    use \xjryanse\bus\service\fixItem\ListTraits;
//    use \xjryanse\bus\service\fixItem\DoTraits;
//    use \xjryanse\bus\service\fixItem\PaginateTraits;

    /**
     * 额外详情
     * @param type $ids
     * @return type
     */
    public static function extraDetails($ids) {
        return self::commExtraDetails($ids, function ($lists) use ($ids) {
                    
                    $con    = [];
                    $con[]  = ['item_name','in',Arrays2d::uniqueColumn($lists, 'item_name')];
                    $arr    = BusFixItemService::where($con)->column('count(1)','item_name');

                    foreach($lists as &$v){
                        // 20240324:维修记录数
                        $v['fixLogCount'] = Arrays::value($arr, $v['item_name']);
                    }
            
                    return $lists;
                }, true);
    }
    
    /**
     * 传入信息，匹配一个id，用于其他表做冗余存储
     */
    public static function matchItem($customerId, $time, $itemName ){
        //①时间+单位，提取有效合同
        $contractIds = ContractService::matchIdsByCustomerAndTime($customerId, $time);
        //②合同+项目名称，提取id
        $con    = [];
        $con[]  = ['contract_id','in',$contractIds];
        $con[]  = ['item_name','=',$itemName];

        $id = self::where($con)->cache(1)->value('id');

        return $id;
    }
    
    public static function test(){
        $customerId = '5275532634163687424';
        $time       = '2024-01-02 00:00:00';
        $itemName   = '救济处理档位';

        $res = self::matchItem($customerId, $time, $itemName);
        dump($res);
        exit;
    }
    
}
