<?php
/**
 * TDevice表
 */
namespace app\model;

use think\facade\Db;

class TDevice extends BaseModel
{
    protected $table = "TDevice";

    /**
     * 获取已注册设备数
     */
    public static function getRegisteredCount()
    {
        $sql = "select count(*) as cnt from TDevice as d
                inner join TComputer as c on d.DeviceID = c.DeviceID
                where c.Registered = 1";

        return self::queryTotal($sql);
    }
}
