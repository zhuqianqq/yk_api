<?php
/**
 * TDevice表
 */
namespace app\model;

use think\facade\Db;

class TPrebroadcast extends BaseModel
{
    protected $table = "t_prebroadcast";

    /**
     * 获取已注册设备数
     */
    public static function getUserPrebroadcastList(int $userid)
    {

        //        return self::queryTotal($sql);
    }
}
