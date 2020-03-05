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

    /**
     * 手机号登录&注册
     */
    public function loginByPhone()
    {
        if ($this->request->isPost()) {

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
}
