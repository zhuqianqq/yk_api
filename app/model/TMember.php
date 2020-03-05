<?php
/**
 * 会员表
 */
namespace app\model;

use app\util\Tools;
use think\facade\Db;

class TMember extends BaseModel
{
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
        $data = self::where("phone",$phone)->field($field)->find();
        return $data ? $data->toArray() : null;
    }


    /**
     * 按手机号注册
     * @param $phone
     */
    public static function registerByPhone($phone)
    {
        $data = [
            'phone'  =>  $phone,
            'nick_name' =>  "nick_".Tools::randStr(10),
            'last_login_time' => date("Y-m-d H:i:s"),
            'create_time' => date("Y-m-d H:i:s"),
        ];
        $user_id = Db::table("t_member")->insert($data);

        return $user_id;
    }

    public static function registerByOpenId($openid,$avatar,$city,$country,$gender,$nick_name,$province)
    {
        $data = [
            'openid' => $openid,
            'nick_name' => $nick_name,
            'avatar' => $avatar,
            'city' => $city,
            'country' => $country,
            'sex' => $gender,
            'province' => $province,
            'last_login_time' => date("Y-m-d H:i:s"),
            'create_time' => date("Y-m-d H:i:s"),
        ];
        $user_id = Db::table("t_member")->insert($data);
        $display_code = 100000 + intval($user_id);//显示编码
            TMember::where([
                "user_id" => $data["user_id"],
            ])->update([
                "display_code" => $display_code, //显示编码
                "last_login_time" => date("Y-m-d H:i:s")
            ]);
        return $user_id;
    }

    /**
     * 商品详情
     * @param $prod_id
     * @return array|null
     */
    public static function getDetail($prod_id)
    {
        $where = ["p.prod_id" => $prod_id];

        $data = Db::table("t_product p")
                ->leftJoin("t_product_detail pd","p.prod_id = pd.prod_id")
                ->field("p.prod_id,p.prod_name,p.first_img,p.price,p.stock,p.weight,p.wechat,
                         p.user_id,p.is_online,p.is_del,pd.head_img,pd.detail_desc,pd.detail_imgs")
                ->where($where)
                ->find();

        if($data){
            $data["head_img"] = $data["head_img"] ? explode(";",$data['head_img']) : [];
            $data["detail_desc"] = $data["detail_desc"] ?? '';
            $data["detail_imgs"] = $data["detail_imgs"] ? explode(";",$data['detail_imgs']) : [];
        }
        return $data;
    }
}
