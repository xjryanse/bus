<?php
namespace xjryanse\bus\service\fixApply;

use xjryanse\approval\service\ApprovalThingService;
use xjryanse\user\service\UserService;
use xjryanse\bus\service\BusService;
use xjryanse\logic\Arrays;
use xjryanse\logic\Debug;

use think\facade\Request;
use Exception;
/**
 * 
 */
trait ApprovalTraits{

    /**
     * 20230704:接口规范写法
     * @return type
     */
    public function approvalAdd() {
        $infoArr    = $this->get();
        $exiApprId  = ApprovalThingService::belongTableIdToId($this->uuid);
        // 20240407
        $rqParam    = Request::param('table_data') ? : Request::param();
        $infoArr['nextAuditUserId'] = Arrays::value($rqParam, 'nextAuditUserId');
        // 20240731:指明了审批节点。
        $infoArr['nextAuditNodeKey'] = Arrays::value($rqParam, 'nextAuditNodeKey');

        //已有直接写，没有的加审批
        $data['approval_thing_id']  = $exiApprId ?: self::addAppr($infoArr);
        $data['need_appr']          = 1;
        return $this->updateRam($data);
    }

    /**
     * 事项提交去审批
     */
    protected static function addAppr($data) {
        $sData                      = Arrays::getByKeys($data, ['dept_id','nextAuditUserId']);
        $sData['user_id']           = session(SESSION_USER_ID);
        $sData['belong_table']      = self::getTable();
        $sData['belong_table_id']   = $data['id'];
        $sData['userName']          = UserService::getInstance($sData['user_id'])->fRealName();

        $busInfo                    = BusService::getInstance($data['bus_id'])->get();
        $sData['licencePlateSeats'] = Arrays::value($busInfo, 'licencePlateSeats');
        // 20230907:改成ram
        $thingCate = 'busFixApply';

//        if(Debug::isDevIp()){
//            $thingCate = 'busFixApplyTest';
//        }

        return ApprovalThingService::thingCateAddApprRam($thingCate, $data['user_id'], $sData);
    }
    /**
     * 20240731:发起审批前，提取一些数据
     * 
     */
    public static function apprAddPreGet($param){
        // 当没有审批单，提取第二级；有审批单，提取下一级
        // 第一级为当前用户。
        $data['id']                 = Arrays::value($param, 'id');
        $data['thisAuditNodeKey']   = 'apprAuditDeptFixManager';

        $data['nextAuditUserId']    = '';
        return $data;
    }
    
}
