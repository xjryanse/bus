<?php
namespace xjryanse\bus\service\oiling;

use xjryanse\logic\Arrays;
use app\order\service\OrderBaoBusService;
/**
 * 计算逻辑
 */
trait CalTraits{
    /**
     * 计算末次加油id
     */
    public function calLastOilingId(){
        $info       = $this->get();
        $busId      = Arrays::value($info, 'bus_id');
        $thisTime   = Arrays::value($info, 'time');
        return self::lastOilingId($busId, $thisTime);
    }
    /**
     * 计算是否有在途趟次
     * @createTime 2023-11-03
     */
    public function calOnRoadBaoBusIds(){
        $time   = $this->fTime();
        $busId  = $this->fBusId();
        $con    = [];
        $con[]  = ['bus_id','=',$busId];
        $con[]  = ['start_time','<=',$time];
        $con[]  = ['end_time','>=',$time];
        $baoBusIds = OrderBaoBusService::where($con)->column('id');

        return $baoBusIds;
    }
    
    /**
     * 20240731：用于公里表提取冗余
     * @return type
     */
    public function calBaoBusId(){
        return $this->fBaoBusId();
    }
}
