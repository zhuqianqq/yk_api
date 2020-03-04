<?php
/**
 * 登录
 */
namespace app\controller;

use think\Session;
use app\util\CaptchaHelper;
use think\facade\Config;

class LoginController extends BaseController
{
    protected $checkLogin = false;

    // 登录页
    public function index()
    {
        if ($this->isLogin()) {
            $this->redirect($this->entranceUrl . "/index");
        }

        return view("index",[
            "data" => [],
        ]);
    }

    /**
     * 登录提交
     */
    public function sendLogin()
    {
        if ($this->request->isAjax()) {
            $userModel = new \app\model\UserModel();
            $data = $this->request->post();
            if ($userModel->sendLogin($data)) {
                return $this->outJson(0,'登录成功',[
                    'url' => $this->entranceUrl . '/index/index',
                ]);
            } else {
                return $this->outJson(1,$userModel->getError());
            }
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

    /**
     * 验证码
     */
    public function captcha()
    {
        $captcha = new CaptchaHelper(Config::get('captcha'));
        $captcha->entry();
    }
}
