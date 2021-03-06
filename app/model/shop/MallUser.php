<?php
/**
 * 商城用户表
 */
namespace app\model\shop;

use app\model\shop\MallBaseModel;
use app\model\TMember;
use app\util\Tools;
use think\facade\Db;

class MallUser extends MallBaseModel
{
    protected $table = "mall_users";

    /**
     * @var string 主键
     */
    protected $pk = 'userId';

    /**
     * 普通会员
     */
    const USER_TYPE_NORMAL = 0;

    /**
     * 商家
     */
    const USER_TYPE_SELLER = 1;

    /**
     * 获取商城用户信息
     * @param int $user_id 商城user_id
     * @param string $field
     */
    public static function getInfoByUserId($user_id,$field = "*")
    {
        return self::where("userId",$user_id)->field($field)->find();
    }

    /**
     * 获取商城用户信息
     * @param string $login_name 商城用户名或手机号
     * @param string $field
     */
    public static function getInfoByLoginName($login_name,$field = "*")
    {
        return self::where("loginName|userPhone",$login_name)->field($field)->find();
    }

    /**
     * 注册
     * @param array $data
     */
    public static function register($data)
    {
        $db_mall = Db::connect("mall");
        $db_mall->startTrans();
        $user_name = !empty($data["phone"]) ? $data["phone"] : $data["display_code"];
        $nick_name = $data["nick_name"] ?? $data["phone"] ?? $data["display_code"];

        $obj = self::getInfoByLoginName($user_name,"userId");
        if(empty($obj)){
            $salt = mt_rand(1000,9999);
            $pwd = Tools::randStr(8); //随机密码
            $insert_data = [
                "userId" => $data["user_id"], //指定userId
                "loginName" => $user_name, //登录账号
                "userName" => $nick_name,
                "userType" => self::USER_TYPE_NORMAL, //会员类型: 0:普通会员,1:商家
                "loginSecret" => $salt, //密码盐值
                "loginPwd" => self::genPasswd($pwd,$salt),
                'userPhoto' => $data["avatar"] ?? '', //头像
                'userPhone' => $data["phone"] ?? '',
                'userSex' => $data['sex'] ?? 0,
                "createTime" => date("Y-m-d H:i:s"),
                'lastTime' => date("Y-m-d H:i:s"),
                'lastIP' => Tools::getClientIp(),
            ];
            $user_id = self::insertGetId($insert_data);
            Tools::addLog("mall_user","register user_id:{$user_id}");
        }else{
            $user_id = $obj["userId"];
            Tools::addLog("mall_user","exist username:{$user_name},user_id:{$user_id}");
        }

        $db_mall->commit();

        return $user_id;
    }

    /**
     * 生成默认密码
     * @param $pwd
     * @return string
     */
    public static function genPasswd($pwd,$salt)
    {
        return md5($pwd . $salt);
    }
}
