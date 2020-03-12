<?php
/**
 * 商城用户表
 */
namespace app\model\shop;

use app\model\shop\ShopBaseModel;
use app\model\TMember;
use app\util\Tools;
use think\facade\Db;
use app\model\TUserMap;

class DscUser extends ShopBaseModel
{
    protected $table = "dsc_users";

    /**
     * 密码盐值
     */
    const EC_SALT = 'ygshop@888';

    /**
     * 获取商城用户信息
     * @param int $user_id 商城user_id
     * @param string $field
     */
    public static function getInfoByUserId($user_id,$field = "*")
    {
        return self::where("user_id",$user_id)->field($field)->find();
    }

    /**
     * 获取商城用户信息
     * @param string $user_name 商城用户名或手机号
     * @param string $field
     */
    public static function getInfoByUserName($user_name,$field = "*")
    {
        return self::where("user_name",$user_name)->field($field)->find();
    }

    /**
     * 注册
     * @param array $data
     */
    public static function register($data)
    {
        $db_shop = Db::connect("shop");
        $db_shop->startTrans();
        $user_name = $data["phone"] ? $data["phone"] : $data["display_code"];
        $nick_name = $data["nick_name"] ?? $data["phone"] ?? $data["display_code"];

        $obj = self::getInfoByUserName($user_name,"user_id");
        if(empty($obj)){
            $insert_data = [
                'user_name' => $user_name, //登录账号
                "nick_name" => $nick_name,
                "ec_salt" => self::EC_SALT, //密码盐值
                "password" => self::genPasswd($data["display_code"]),
                'user_picture' => $data["avatar"] ?? '', //头像
                'mobile_phone' => $data["phone"] ?? '',
                'sex' => $data['sex'] ?? 0,
                "reg_time" => time(),
                'last_login' => time(),
                'last_ip' => Tools::getClientIp(),
            ];
            $shop_user_id = self::insertGetId($insert_data);
        }else{
            $shop_user_id = $obj["user_id"];
        }

        if($shop_user_id){
            //存于映射表
            TUserMap::addMap($data["user_id"],$shop_user_id);
        }
        $db_shop->commit();

        return $shop_user_id;
    }


    /**
     * 生成默认密码
     * @param $pwd
     * @return string
     */
    public static function genPasswd($pwd)
    {
        return md5(md5($pwd) . self::EC_SALT);
    }
}
