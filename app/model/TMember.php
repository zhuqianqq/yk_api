<?php
/**
 * 会员表
 */
namespace app\model;

use app\util\Tools;
use think\facade\Db;

class TMember extends BaseModel
{
    /**
     * 是否主播
     */
    const IS_BROADCASTER_YES = 1; // 是
    const IS_BROADCASTER_NO = 0;  // 否

    protected $table = "t_member";

    /**
     * @var array 是否锁定
     */
    public static $IS_LOCk_ARR = [
        "1" => "锁定",
        "0" => "正常"
    ];

    /**
     * @var array 是否已实名
     */
    public static $AUDIT_STATUS_ARR = [
        "1" => "是",
        "0" => "否"
    ];

    /**
     * @param $phone
     */
    public static function getByPhone($phone,$field = "*")
    {
        $data = self::where("phone",$phone)->field($field)->find();

        return $data ? $data->toArray() : null;
    }


    public static function getByOpenId($openid, $field = "*"){
        $data = self::where("openid",$openid)->field($field)->find();
        return $data ? $data->toArray() : null;
    }

    /**
     * 随机生成昵称
     */
    public static function generateNick($prefix = "ygzb_")
    {
        return $prefix.Tools::randStr(4);
    }


    /**
     * @param $user_id
     * @return int
     */
    public static function generateDisplayCode($user_id)
    {
        return 100000 + intval($user_id);
    }

    /**
     * 按手机号注册
     * @param $phone
     */
    public static function registerByPhone($phone)
    {
        $data = [
            'phone'  =>  $phone,
            'nick_name' => self::generateNick(),
            'last_login_time' => date("Y-m-d H:i:s"),
            'create_time' => date("Y-m-d H:i:s"),
        ];
        $user_id = Db::table("t_member")->insert($data);

        return $user_id;
    }

    public static function registerByOpenId($openid)
    {
        $data = [
            'openid' => $openid,
            'nick_name' => self::generateNick(),
            'last_login_time' => date("Y-m-d H:i:s"),
            'create_time' => date("Y-m-d H:i:s"),
        ];
        $user_id = Db::table("t_member")->insert($data);
        return $user_id;
    }
}
