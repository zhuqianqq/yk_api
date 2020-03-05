<?php
/**
 * 登录
 */
namespace app\controller;

use think\Session;
use app\util\ValidateHelper;
use app\model\TMember;
use think\facade\Db;
use think\facade\Cache;
use app\util\AccessKeyHelper;
use app\util\SmsHelper;

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
            $user_data = TMember::getByPhone($phone);
            if(!$user_data){
                //注册
                $user_id = TMember::registerByPhone($phone);
                if($user_id <= 0){
                    return $this->outJson(200,"注册失败");
                }
                $user_data = TMember::getByPhone($phone);
            }
            if ($user_data["is_lock"] == 1) {
                return $this->outJson(200,"账号已被锁定");
            }
            unset($user_data["password"]);

            TMember::where([
                "user_id" => $user_data["user_id"],
            ])->update([
                "last_login_time" => date("Y-m-d H:i:s")
            ]);
            Db::commit();
            $user_data["access_key"] = AccessKeyHelper::generateAccessKey($user_data["user_id"]); //生成access_key

            return $this->outJson(0,"登录成功",$user_data);
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
