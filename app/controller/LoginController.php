<?php
/**
 * 登录
 */
namespace app\controller;

use think\facade\Config;
use think\Session;
use app\util\ValidateHelper;
use app\model\TMember;
use think\facade\Db;
use think\facade\Cache;
use app\util\AccessKeyHelper;
use app\util\SmsHelper;
use app\util\WechatHelper;
use app\util\TLSSigAPIv2;

class LoginController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['loginOut']],
    ];

    /**
     * 手机号登录&注册
     */
    public function loginByPhone()
    {
        $phone = $this->request->post("phone",'',"intval");
        $vcode = $this->request->post("vcode",'',"intval");

        if(ValidateHelper::isMobile($phone) == false || $vcode <= 0){
            return $this->outJson(100,"参数错误");
        }

        if(SmsHelper::checkVcode($phone,$vcode,"login") == false){
            return $this->outJson(100,"验证码无效");
        }

        try{
            Db::startTrans();
            $fields = "user_id,phone,nick_name,sex,avatar,front_cover,openid,country,province,city,display_code,is_broadcaster,audit_status,is_lock";
            $data = TMember::getByPhone($phone,$fields);
            if(!$data){
                //注册
                $user_id = TMember::registerByPhone($phone);
                if($user_id <= 0){
                    return $this->outJson(200,"注册失败");
                }
                $data = TMember::getByPhone($phone,$fields);
            }
            if ($data["is_lock"] == 1) {
                return $this->outJson(200,"账号已被锁定");
            }
            $display_code = TMember::generateDisplayCode($data["user_id"]);//显示编码
            TMember::where([
                "user_id" => $data["user_id"],
            ])->update([
                "display_code" => $display_code, //显示编码
                "last_login_time" => date("Y-m-d H:i:s")
            ]);
            Db::commit();
            $data = TMember::getByPhone($phone,$fields);
            $data["access_key"] = AccessKeyHelper::generateAccessKey($data["user_id"]); //生成access_key

            $im_config = Config::get('im');
            $api = new TLSSigAPIv2($im_config["IM_SDKAPPID"], $im_config["IM_SECRETKEY"]);
            $user_sign = $api->genSig($display_code);

            $data["room_sign"] = [
                "sdk_appid" => intval($im_config["IM_SDKAPPID"]),
                "display_code" => $display_code,
                "user_sign" => $user_sign
            ];
            return $this->outJson(0,"登录成功",$data);
        }catch (\Exception $ex){
            Db::rollback();
            return $this->outJson(500,"接口异常:".$ex->getMessage());
        }
    }

    /**
     * 小程序登录
     * @return array
     */
    public function loginByMinWechat()
    {
        $code = $this->request->post("code");
        $avatar = $this->request->post("avatar");
        $city = $this->request->post("city");
        $country = $this->request->post("country");
        $gender = $this->request->post("gender");
        $nick_name = $this->request->post("nick_name");
        $province = $this->request->post("province");

        $openid = WechatHelper::getWechatOpenId($code);
        if ($openid == "") {
            return $this->outJson(200, "获取微信信息失败！");
        }
        $fields = "user_id,phone,nick_name,sex,avatar,front_cover,openid,country,province,city,display_code,is_broadcaster,audit_status,is_lock";
        $data = TMember::getByOpenId($openid, $fields);
        if (!$data) {
            $user_id = TMember::registerByOpenId($openid);
            if ($user_id <= 0) {
                return $this->outJson(200, "注册失败");
            }
            $data = TMember::getByOpenId($openid, $fields);
        }
        $display_code = TMember::generateDisplayCode($data["user_id"]);//显示编码
        TMember::where([
            "user_id" => $data["user_id"],
        ])->update([
            'nick_name' => empty($nick_name) ? $data['nick_name'] : $nick_name,
            'avatar' => empty($avatar) ? $data['avatar'] : $avatar,
            'city' => empty($city) ? $data['city'] : $city,
            'country' => empty($country) ? $data['country'] : $country,
            'sex' => $gender > 0 ? $gender : $data['sex'],
            'province' => empty($province) ? $data['province'] : $province,
            "display_code" => empty($data['display_code']) ? $display_code : $data['display_code'],
            "last_login_time" => date("Y-m-d H:i:s")
        ]);
        if ($data["is_lock"] == 1) {
            return $this->outJson(200, "账号已被锁定");
        }

        $data["access_key"] = AccessKeyHelper::generateAccessKey($data["user_id"]); //生成access_key

        $im_config = Config::get('im');
        $api = new TLSSigAPIv2($im_config["IM_SDKAPPID"], $im_config["IM_SECRETKEY"]);
        $user_sign = $api->genSig($display_code);

        $data["room_sign"] = [
            "sdk_appid" => intval($im_config["IM_SDKAPPID"]),
            "display_code" => $display_code,
            "user_sign" => $user_sign
        ];
        return $this->outJson(0, "登录成功", $data);
    }

    public function loginByWechat()
    {
        $avatar = $this->request->post("avatar");
        $city = $this->request->post("city");
        $country = $this->request->post("country");
        $gender = $this->request->post("gender");
        $nick_name = $this->request->post("nick_name");
        $province = $this->request->post("province");
        $openid = $this->request->post("openid");
        $fields = "user_id,phone,nick_name,sex,avatar,front_cover,openid,country,province,city,display_code,is_broadcaster,audit_status,is_lock";
        $data = TMember::getByOpenId($openid, $fields);
        if (!$data) {
            $user_id = TMember::registerByOpenId($openid);
            if ($user_id <= 0) {
                return $this->outJson(200, "注册失败");
            }
            $data = TMember::getByOpenId($openid, $fields);
        }
        $display_code = TMember::generateDisplayCode($data["user_id"]);//显示编码
        TMember::where([
            "user_id" => $data["user_id"],
        ])->update([
            'nick_name' => empty($nick_name) ? $data['nick_name'] : $nick_name,
            'avatar' => empty($avatar) ? $data['avatar'] : $avatar,
            'city' => empty($city) ? $data['city'] : $city,
            'country' => empty($country) ? $data['country'] : $country,
            'sex' => $gender > 0 ? $gender : $data['sex'],
            'province' => empty($province) ? $data['province'] : $province,
            "display_code" => empty($data['display_code']) ? $display_code : $data['display_code'],
            "last_login_time" => date("Y-m-d H:i:s")
        ]);
        $data = TMember::getByOpenId($openid, $fields);
        if ($data["is_lock"] == 1) {
            return $this->outJson(200, "账号已被锁定");
        }

        $data["access_key"] = AccessKeyHelper::generateAccessKey($data["user_id"]); //生成access_key

        $im_config = Config::get('im');
        $api = new TLSSigAPIv2($im_config["IM_SDKAPPID"], $im_config["IM_SECRETKEY"]);
        $user_sign = $api->genSig($display_code);

        $data["room_sign"] = [
            "sdk_appid" => intval($im_config["IM_SDKAPPID"]),
            "display_code" => $display_code,
            "user_sign" => $user_sign
        ];
        return $this->outJson(0, "登录成功", $data);
    }

    public function bindPhone()
    {
        $vcode = $this->request->post("vcode", 0, "intval");
        $phone = $this->request->post("phone", 0, "intval");
        $user_id = $this->request->post("user_id", 0, "intval");

        if (ValidateHelper::isMobile($phone) == false || $vcode <= 0) {
            return $this->outJson(100, "参数错误");
        }

        if (SmsHelper::checkVcode($phone, $vcode, "login") == false) {
            return $this->outJson(100, "验证码无效");
        }

        TMember::where([
            "user_id" => $user_id,
        ])->update([
            'phone' => $phone,
            "last_login_time" => date("Y-m-d H:i:s")
        ]);
        return $this->outJson(0, "绑定成功");
    }
    
    /**
     * 退出登录
     */
    public function loginOut()
    {
        if($this->user_id){
            AccessKeyHelper::forgetAccessKey($this->user_id);
        }
        return $this->outJson(0, "success");
    }
}
