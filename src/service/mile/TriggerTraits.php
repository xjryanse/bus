<?php
namespace xjryanse\bus\service\mile;

use xjryanse\logic\DataCheck;
use xjryanse\logic\Arrays;
use xjryanse\logic\DbOperate;
use xjryanse\logic\Debug;
/**
 * 触发复用
 */
trait TriggerTraits{
    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        $keys = ['bus_id','handle_mile'];
        DataCheck::must($data, $keys);
        
        self::redunFields($data, $uuid);
    }

    /**
     * 钩子-保存后
     */
    public static function ramAfterSave(&$data, $uuid) {
        
    }

    /**
     * 钩子-更新前
     */
    public static function ramPreUpdate(&$data, $uuid) {
        
        self::redunFields($data, $uuid);
    }

    /**
     * 钩子-更新后
     */
    public static function ramAfterUpdate(&$data, $uuid) {
        
    }

    /**
     * 钩子-删除前
     */
    public function ramPreDelete() {
        
    }

    /**
     * 钩子-删除后
     */
    public function ramAfterDelete() {
        
    }

    protected static function redunFields(&$data, $uuid){
        $fromTable      = Arrays::value($data, 'from_table');
        $fromTableId    = Arrays::value($data, 'from_table_id');
        // $fromTable      = Arrays::value($data, 'from_table') ? : self::getInstance($uuid)->fFromTable();
        // $fromTableId    = Arrays::value($data, 'from_table_id') ? : self::getInstance($uuid)->fFromTableId();
        if($fromTable && $fromTableId){
            $service        = DbOperate::getService($fromTable);
            // 20240731:固定方法名
            if(method_exists($service, 'calBaoBusId')){
                $data['bao_bus_id'] = $service::getInstance($fromTableId)->calBaoBusId();
            }
        }
        // Debug::dump($data);
        return $data;
    }
}
