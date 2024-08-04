<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\DataCheck;

/**
 * 
 */
class BusMileService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;


    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusMile';

    use \xjryanse\bus\service\mile\FieldTraits;
    use \xjryanse\bus\service\mile\TriggerTraits;
    
    /**
     * 添加车辆里程数
     * @param type $busId
     * @param type $mile
     * @param type $time
     * @param type $fromTable
     * @param type $fromTableId
     * @param type $data
     */
    public static function mileSyncGetId($busId, $mile, $time = '', $fromTable = '', $fromTableId = '', $data = []){
        $data['bus_id']         = $busId;
        $data['handle_mile']    = $mile;
        $data['upl_time']       = $time ? : date('Y-m-d H:i:s');
        
        if(self::fromTableHasRecord($fromTable, $fromTableId)){
            $id = self::fromTableRecordId($fromTable, $fromTableId);
            self::getInstance($id)->updateRam($data);
            return $id;
        }

        $data['from_table'] = $fromTable;
        $data['from_table_id'] = $fromTableId;

        return self::saveGetIdRam($data);
    }
    
    protected static function fromTableHasRecord($fromTable, $fromTableId){
        // 20231205:没记录id则不存在
        return self::fromTableRecordId($fromTable, $fromTableId) ? true : false;
    }
    
    protected static function fromTableRecordId($fromTable, $fromTableId){
        // 20231205:没记录id则不存在
        if(!$fromTableId){
            return '';
        }

        $con    = [];
        $con[]  = ['from_table','=',$fromTable];
        $con[]  = ['from_table_id','=',$fromTableId];
        
        return self::where($con)->cache(1)->value('id');
    }
    /*
     * 来源表+来源表id删除
     */
    public static function deleteByFromTableAndFromTableId($fromTable, $fromTableId){
        if(!$fromTable || !$fromTableId){
            return false;
        }
        $con[] = ['from_table','=',$fromTable];
        $con[] = ['from_table_id','=',$fromTableId];
        
        $lists = self::where($con)->select();
        foreach($lists as $v){
            self::getInstance($v['id'])->deleteRam();
        }
    }
    

}
