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
use app\util\TLSSigAPIv2;

class LoginController extends BaseController
{
    protected $checkLogin = false;

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
            //return $this->outJson(100,"验证码错误");
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
            $display_code = 100000 + intval($data["user_id"]);//显示编码
            TMember::where([
                "user_id" => $data["user_id"],
            ])->update([
                "display_code" => $display_code, //显示编码
                "last_login_time" => date("Y-m-d H:i:s")
            ]);
            Db::commit();
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
     * 退出登录
     */
    public function loginOut()
    {
        if ($this->request->isAjax()) {
            if($this->getCurrentUserId() != ''){
                @Session::clear();
                @Session::destroy();
            }
            return $this->outJson(0,'退出成功',[
                'url' => $this->entranceUrl . "/login/index"
            ]);
        }
    }
}
