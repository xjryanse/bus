<?php
namespace xjryanse\bus\service\index;

use xjryanse\bus\service\BusTypeService;
/**
 * 触发复用
 */
trait CalTraits{

    /**
     * 计算车型的level(算工资)
     * @describe 十几座一档；二十几一档……
     * @useFul 1
     */
    public function calBusTypeLevel(){
        $busType =$this->fBusType();
        $level = BusTypeService::getInstance($busType)->fLevel();
        return $level;
    }

}
