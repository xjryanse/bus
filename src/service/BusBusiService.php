<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Arrays;
/**
 * 
 */
class BusBusiService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;


    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusBusi';

    // use \xjryanse\bus\service\mile\FieldTraits;
    // use \xjryanse\bus\service\mile\TriggerTraits;

    /**
     * 车辆证件key
     */
    public static function busCertKeys($busiType){
        $con[]      = ['busi_type','=',$busiType];
        $info       = self::where($con)->find();
        $certKeys   = Arrays::value($info, 'bus_cert_keys');
        return explode(',', $certKeys);
    }
    /**
     * 司机证件key
     */
    public static function driverCertKeys($busiType){
        $con[] = ['busi_type','=',$busiType];
        $info   = self::where($con)->find();
        $certKeys   = Arrays::value($info, 'driver_cert_keys');
        return explode(',', $certKeys);
    }
    
    
}
