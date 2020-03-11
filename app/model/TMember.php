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

    const DISPLAY_CODE_DIFF = 100000;

    const DEFAULT_AVATAR = 'https://img.ikstatic.cn/MTU4MzQ5MDczNTAwMCM0NjMjcG5n.png';

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
     * 根据手机号
     * @param $phone
     * @param $field
     */
    public static function getByPhone($phone, $field = "")
    {
        if (empty($field)) {
            $field = "user_id,phone,nick_name,sex,avatar,front_cover,openid,country,province,city,display_code,
                       is_broadcaster,audit_status,is_lock";
        }
        $data = self::where("phone", $phone)->field($field)->find();

        return $data ? $data->toArray() : null;
    }


    /**
     * 根据opendid获取
     * @param string $openid
     * @param string $field
     * @return array|null
     */
    public static function getByOpenId($openid, $field = "")
    {
        if (empty($field)) {
            $field = "user_id,phone,nick_name,sex,avatar,front_cover,openid,country,province,city,display_code,
                       is_broadcaster,audit_status,is_lock";
        }
        $data = self::where("openid", $openid)->field($field)->find();

        return $data ? $data->toArray() : null;
    }

    /**
     * 生成昵称
     */
    public static function generateNick($display_code, $prefix = "映购")
    {
        return $prefix . $display_code;
    }


    /**
     * @param $user_id
     * @return int
     */
    public static function generateDisplayCode($user_id)
    {
        return self::DISPLAY_CODE_DIFF + intval($user_id);
    }

    /**
     * 根据display_code返回user_id
     * @param $display_code
     * @return int
     */
    public static function getUserIdByDisplayCode($display_code)
    {
        return intval($display_code) - self::DISPLAY_CODE_DIFF;
    }

    /**
     * 按手机号注册
     * @param $phone
     */
    public static function registerByPhone($phone)
    {
        $data = [
            'phone' => $phone,
            'last_login_time' => date("Y-m-d H:i:s"),
            'create_time' => date("Y-m-d H:i:s"),
            'avatar' => self::DEFAULT_AVATAR, //默认头像
        ];
        $user_id = self::insertGetId($data);

        if ($user_id) {
            self::updateOtherInfo($user_id);
        }

        return $user_id;
    }

    /**
     * 更新nick和display_code
     * @param $user_id
     */
    private static function updateOtherInfo($user_id)
    {
        $display_code = self::generateDisplayCode($user_id);//显示编码
        $nick_name = self::generateNick($display_code);
        $up_data = [
            "display_code" => $display_code,
            "nick_name" => $nick_name,
        ];
        self::where("user_id", $user_id)->update($up_data);
    }

    /**
     * 按open_id注册
     * @param $openid
     * @return int|string
     */
    public static function registerByOpenId($openid)
    {
        $data = [
            'openid' => $openid,
            'last_login_time' => date("Y-m-d H:i:s"),
            'create_time' => date("Y-m-d H:i:s"),
        ];

        $user_id = self::insertGetId($data);

        if ($user_id) {
            self::updateOtherInfo($user_id);
        }

        return $user_id;
    }

    /**
     * 禁播
     */
    public static function forbidMember($user_id,$room_id,$end_time, $reason,$oper_user='system')
    {
        TMember::where("user_id", $user_id)->update([
            "is_forbid" => 1,
            "forbid_reason" => $reason,
            "forbid_end_time" => date("Y-m-d H:i:s", $end_time),
        ]);
        $oper_log = new TRoomOperLog();
        $oper_log->save([
            "room_id" => $room_id,
            "user_id" => $user_id,
            "oper_user" => $oper_user,
            "oper" => 0, //0：禁播，1：解播
            "forbid_end_time" => date("Y-m-d H:i:s", $end_time),
            "reason" => $reason,
            "create_time" => date("Y-m-d H:i:s"),
        ]);
    }

    public static function unforbidMember($user_id,$reason,$oper_user='system')
    {
        TMember::where("user_id",$user_id)->update([
            "is_forbid" => 0,
            "forbid_reason" => '',
            "forbid_end_time" => null,
        ]);
        $oper_log = new TRoomOperLog();
        $oper_log->save([
            "room_id" => '',
            "user_id" => $user_id,
            "oper_user" => $oper_user,
            "oper" => 1, //0：禁播，1：解播
            "reason" => $reason,
            "create_time" => date("Y-m-d H:i:s"),
        ]);
    }
}
