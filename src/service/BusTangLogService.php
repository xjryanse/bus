<?php

namespace xjryanse\bus\service;

use xjryanse\system\interfaces\MainModelInterface;
use xjryanse\logic\Debug;
use xjryanse\logic\Cachex;
use xjryanse\logic\Arrays2d;
use xjryanse\logic\DbOperate;
use think\Db;

/**
 * 
 */
class BusTangLogService extends Base implements MainModelInterface {

    use \xjryanse\traits\InstTrait;
    use \xjryanse\traits\MainModelTrait;
    use \xjryanse\traits\MainModelRamTrait;
    use \xjryanse\traits\MainModelCacheTrait;
    use \xjryanse\traits\MainModelCheckTrait;
    use \xjryanse\traits\MainModelGroupTrait;
    use \xjryanse\traits\MainModelQueryTrait;

    use \xjryanse\traits\MainStaticsTrait;

    protected static $mainModel;
    protected static $mainModelClass = '\\xjryanse\\bus\\model\\BusTangLog';

    /**
     * 20220917:趟检关联
     * 趟检后未出车，可以继续有效
     * 出车后24小时内有效；
     * 长途跨天按趟算。
     */
    public static function tangSync() {
        //根据趟检时间，提取最近一次的出车时间。
    }

    /**
     * 根据日期获取车辆日趟检记录
     * @param type $date
     * @return type
     */
    public static function getTangByDate($date) {
        $busTable = BusService::getTable();
        $tangLogTable = self::getTable();

        $companyId = session('scopeCompanyId');
        $sql = "SELECT a.id,a.licence_plate,a.seats,a.licencePlateSeats,b.belong_date,b.has_tang 
            FROM " . $busTable . " AS a
                LEFT JOIN (select * from " . $tangLogTable . " where belong_date = '" . $date . "') AS b ON a.id = b.bus_id 
            WHERE 
                a.company_id='" . $companyId . "' and a.status = 1 and a.owner_type = 'self' and (b.belong_date = '" . $date . "' or b.belong_date is null) ORDER BY has_tang desc,passenger_max DESC";
        // Debug::debug('getWashByDate的$sql',$sql);
        $res = Db::query($sql);
        foreach ($res as &$v) {
            $v['seats'] = $v['seats'] ? $v['seats'] . '座' : '';
            // 20230526
            $v['belong_date'] = $v['belong_date'] ? $v['belong_date'] : $date;
        }
        return $res;
    }

    /**
     * 0919:按日获取趟检记录数组
     */
    public static function dateTangLogArr($date) {
        $cacheKey = __METHOD__ . $date;
        return Cachex::funcGet($cacheKey, function() use ($date) {
                    $con[] = ['tang_time', '>=', date('Y-m-d 00:00:00', strtotime($date))];
                    $con[] = ['tang_time', '<=', date('Y-m-d 23:59:59', strtotime($date))];
                    $lists = self::lists($con, 'tang_time desc');
                    $listsArr = $lists ? $lists->toArray() : [];
                    return $listsArr;
                }, true, 60);
    }

    /**
     * 获取趟检记录
     */
    public static function tangLogsArr($busIds, $minTime, $maxTime) {
        // 趟检单24小时有效
        $cone[] = ['bus_id', 'in', $busIds];
        $cone[] = ['tang_time', '>=', $minTime];
        $cone[] = ['tang_time', '<', $maxTime];
        $tangLogs = self::lists($cone);
        $arr = $tangLogs ? $tangLogs->toArray() : [];
        return $arr;
    }

    /**
     * 车辆的末次趟检时间
     */
    public static function busLastTangLog($busId, $time = '') {
        if (!$time) {
            $time = date('Y-m-d H:i:s');
        }
        $date = date('Y-m-d', strtotime($time));
        $todayTangArr = self::dateTangLogArr($date);
        $con[] = ['bus_id', '=', $busId];
        $con[] = ['tang_time', '<=', $time];
        $lastTang = Arrays2d::listFind($todayTangArr, $con);
        if (!$lastTang) {
            $yesterdayDate = date('Y-m-d', strtotime($time) - 86400);
            $yesterdayTangArr = self::dateTangLogArr($yesterdayDate);
            $lastTang = Arrays2d::listFind($yesterdayTangArr, $con);
        }
        return $lastTang;
    }

    public static function staticsBusByMonth($con = [], $orderBy = '') {
        return self::staticsBus('month', $con, $orderBy);
    }

    /**
     * 按年统计驾驶员信息
     * @param type $con
     * @return type
     */
    public static function staticsBusByYear($con = [], $orderBy = "") {
        return self::staticsBus('year', $con, $orderBy);
    }

    /**
     * 20220922:按车辆聚合查询
     * @param type $staticsBy
     * @param type $con
     * @param type $orderBy
     * @return type
     */
    protected static function staticsBus($staticsBy = 'date', $con = [], $orderBy = '') {
        //调用公共聚合查询逻辑
        return self::commStaticsTimeGroup($staticsBy, $con, function($con, $groupField, $orderByStr) {
                    $data = self::where($con)
                            ->group("company_id,bus_id,date_format( `tang_time`, '" . $groupField . "' ) " . $orderByStr)
                            ->field("company_id,bus_id,
                        date_format( `tang_time`, '" . $groupField . "' ) as belongTime,
                        count(*) as tangCount")
                            ->select();
                    return $data ? $data->toArray() : [];
                }, $orderBy);
    }

    /**
     * 钩子-保存前
     */
    public static function ramPreSave(&$data, $uuid) {
        
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

    /**
     *
     */
    public function fId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     *
     */
    public function fCompanyId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 车辆id
     */
    public function fBusId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 洗车里程
     */
    public function fBusMile() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 洗车时间
     */
    public function fWashTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 洗车费用
     */
    public function fWashMoney() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 经度,纬度
     */
    public function fWashStationLocation() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 加油站名称
     */
    public function fWashStation() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 司机d
     */
    public function fDriverId() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 上传凭据图片，逗号分隔
     */
    public function fEvidence() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 20220705
     */
    public function fHasWash() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 20220705
     */
    public function fBelongDate() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 排序
     */
    public function fSort() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 状态(0禁用,1启用)
     */
    public function fStatus() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 有使用(0否,1是)
     */
    public function fHasUsed() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 锁定（0：未锁，1：已锁）
     */
    public function fIsLock() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 锁定（0：未删，1：已删）
     */
    public function fIsDelete() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 备注
     */
    public function fRemark() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 创建者，user表
     */
    public function fCreater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 更新者，user表
     */
    public function fUpdater() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 创建时间
     */
    public function fCreateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

    /**
     * 更新时间
     */
    public function fUpdateTime() {
        return $this->getFFieldValue(__FUNCTION__);
    }

}
