<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\bus\service\BusOilingService;
/**
 * 车辆加油记录-出车趟次关联表
 * 多对多
 */
class BusOilingBaoBusService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    
    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusOilingBaoBus';
    //直接执行后续触发动作
    protected static $directAfter = true;

    use \xjryanse\bus\service\oilingBaoBus\CalTraits;
    /**
     * 加油记录，生成关联明细
     * 分摊方式：按公里；按订单时长；按订单金额
     * 
     */
    public static function generateByBusOilingId($busOilingId){
        // 加油记录详细信息
        $time           = BusOilingService::getInstance($busOilingId)->fTime();
        // 提取前一次加油时间
        $lastId         = BusOilingService::getInstance($busOilingId)->fLastId();
        $lastTime       = BusOilingService::getInstance($lastId)->fTime();
        // 提取前一次加满时间
        $lastFullId     = BusOilingService::getInstance($busOilingId)->fLastFullId();
        $lastFullTime   = BusOilingService::getInstance($lastFullId)->fTime();

        dump('本次加油时间');
        dump($time);
        dump('=========');
        dump('上次加油时间');
        dump($lastTime);
        dump('=========');
        dump('上次加满时间');
        dump($lastFullTime);
        // 计算是否有在途订单
        $resp = BusOilingService::getInstance($busOilingId)->calOnRoadBaoBusIds();
        dump('========');
        dump($resp);
        
        // 提取本次加油时间
        
        // 上次加油，有进行中订单，不行，取再上一次
        // 上次加油，未加满，不行，取再上一次。
        
        
        
        // 情况1：上次加油时，无进行中订单，未加满
        // 情况2：上次加油时，有进行中订单，加满或未加满
        // 情况3；
        
        
    }
    
    
}
