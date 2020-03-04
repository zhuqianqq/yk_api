<?php
/**
 * 通用控制器
 */
namespace app\controller;

use think\facade\Session;
use app\util\ValidateCode;
use app\model\TDict;
use app\util\CaptchaHelper;
use think\facade\Config;

class CommonController extends BaseController
{
    protected $checkLogin = false;

    /**
     * 验证码
     */
    public function vcode()
    {
        $type = $this->request->param("type",null);
        $codetype = 3;
        if($type === null){
            $codetype = TDict::getItemValue('AdministratorInfo', 'loginCodeType');//登录验证码类别
            $vc = new ValidateCode($codetype);
            $vc->doImg();
            Session::set("authnum",$vc->getCode());
            exit();
        }

        if($type == "PC"){//PC入网验证码类别
            $codetype = TDict::getItemValue('AuthParam', 'verifyCodeType');
        }else if($type == "Mobile"){ //移动入网验证码类别
            $codetype = TDict::getItemValue('Mobile', 'verifyCodeType');
        }

        $vc = new ValidateCode($codetype);
        $vc->doImg();
        Session::set("verifyCode",$vc->getCode());
    }

    /**
     * 验证码
     */
    public function captcha()
    {
        $captcha = new CaptchaHelper(Config::get('captcha'));
        $captcha->entry();
    }
}
