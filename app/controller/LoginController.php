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
        $phone = $this->request->post("phone", '', "intval");
        $vcode = $this->request->post("vcode", '', "intval");

        if (ValidateHelper::isMobile($phone) == false || $vcode <= 0) {
            return $this->outJson(100, "参数错误");
        }

        if (SmsHelper::checkVcode($phone, $vcode, "login") == false) {
            return $this->outJson(100, "验证码无效");
        }

        try {
            Db::startTrans();
            $data = TMember::getByPhone($phone);

            if (!$data) {
                //注册
                $user_id = TMember::registerByPhone($phone);
                if ($user_id <= 0) {
                    return $this->outJson(200, "注册失败");
                }
                $data = TMember::getByPhone($phone);
            }

            if ($data["is_lock"] == 1) {
                return $this->outJson(200, "账号已被锁定");
            }
            TMember::where([
                "user_id" => $data["user_id"],
            ])->update([
                "last_login_time" => date("Y-m-d H:i:s")
            ]);
            Db::commit();

            TMember::setOtherInfo($data);
            SmsHelper::clearCacheKey($phone,"login");

            return $this->outJson(0, "登录成功", $data);
        } catch (\Exception $ex) {
            Db::rollback();
            return $this->outJson(500, "接口异常:" . $ex->getMessage());
        }
    }



    /**
     * 小程序登录
     */
    public function loginByMinWechat()
    {
        $code = $this->request->post("code", '', "trim");
        $avatar = $this->request->post("avatar", '', "trim");
        $city = $this->request->post("city", '', "trim");
        $country = $this->request->post("country", '', "trim");
        $gender = $this->request->post("gender");
        $nick_name = $this->request->post("nick_name", '', "trim");
        $province = $this->request->post("province", '', "trim");

        $openid = WechatHelper::getWechatOpenId($code); //以code换取openid
        if ($openid == "") {
            return $this->outJson(200, "获取微信openid失败！");
        }
        $data = TMember::getByOpenId($openid);
        if (!$data) {
            $user_id = TMember::registerByOpenId($openid);
            if ($user_id <= 0) {
                return $this->outJson(200, "注册失败");
            }
            $data = TMember::getByOpenId($openid);
        }
        if ($data["is_lock"] == 1) {
            return $this->outJson(200, "账号已被锁定");
        }

        TMember::where([
            "user_id" => $data["user_id"],
        ])->update([
            'nick_name' => empty($nick_name) ? $data['nick_name'] : $nick_name,
            'avatar' => empty($avatar) ? $data['avatar'] : $avatar,
            'city' => empty($city) ? $data['city'] : $city,
            'country' => empty($country) ? $data['country'] : $country,
            'sex' => $gender > 0 ? $gender : $data['sex'],
            'province' => empty($province) ? $data['province'] : $province,
            "last_login_time" => date("Y-m-d H:i:s"),
        ]);

        TMember::setOtherInfo($data);
        $dbData = TMember::getByOpenId($openid);
        return $this->outJson(0, "登录成功", array_merge($data, $dbData));
    }

    /**
     * APP微信登录
     * @return array
     */
    public function loginByWechat()
    {
        $avatar = $this->request->post("avatar", '', "trim");
        $city = $this->request->post("city", '', "trim");
        $country = $this->request->post("country", '', "trim");
        $gender = $this->request->post("gender", '', 'trim');
        $nick_name = $this->request->post("nick_name", '', 'trim');
        $province = $this->request->post("province", '', 'trim');
        $openid = $this->request->post("openid", '', "trim");

        $data = TMember::getByOpenId($openid);

        if (!$data) {
            $user_id = TMember::registerByOpenId($openid);
            if ($user_id <= 0) {
                return $this->outJson(200, "注册失败");
            }
            $data = TMember::getByOpenId($openid);
        }

        if ($data["is_lock"] == 1) {
            return $this->outJson(200, "账号已被锁定");
        }

        TMember::where([
            "user_id" => $data["user_id"],
        ])->update([
            'nick_name' => empty($nick_name) ? $data['nick_name'] : $nick_name,
            'avatar' => empty($avatar) ? $data['avatar'] : $avatar,
            'city' => empty($city) ? $data['city'] : $city,
            'country' => empty($country) ? $data['country'] : $country,
            'sex' => $gender > 0 ? $gender : $data['sex'],
            'province' => empty($province) ? $data['province'] : $province,
            "last_login_time" => date("Y-m-d H:i:s")
        ]);

        TMember::setOtherInfo($data);

        return $this->outJson(0, "登录成功", $data);
    }

    /**
     * 绑定手机号
     * @return array
     */
    public function bindPhone()
    {
        $vcode = $this->request->post("vcode", 0, "intval");
        $phone = $this->request->post("phone", 0, "intval");
        $user_id = $this->request->post("user_id", 0, "intval");

        if (ValidateHelper::isMobile($phone) == false) {
            return $this->outJson(100, "手机号格式错误");
        }

        if (SmsHelper::checkVcode($phone, $vcode, "login") == false) {
            return $this->outJson(100, "验证码无效");
        }

        $exist_user= TMember::where('phone',$phone)->find();
        if($exist_user != null) {
            return $this->outJson(100, "此手机号已绑定其它账号！");
        }

        TMember::where([
            "user_id" => $user_id,
        ])->update([
            'phone' => $phone,
        ]);

        SmsHelper::clearCacheKey($phone,"login");

        return $this->outJson(0, "绑定成功");
    }

    /**
     * 退出登录
     */
    public function loginOut()
    {
        if ($this->user_id) {
            AccessKeyHelper::forgetAccessKey($this->user_id);
        }
        return $this->outJson(0, "success");
    }
}
