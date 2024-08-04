<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;

/**
 * 车辆违章
 */
class BusBreakRulesService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\StaticsModelTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusBreakRules';

    use \xjryanse\bus\service\breakRules\FieldTraits;
    use \xjryanse\bus\service\breakRules\TriggerTraits;

    

}
