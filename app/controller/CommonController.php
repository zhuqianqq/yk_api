<?php
/**
 * 通用控制器
 */

namespace app\controller;

use app\util\SmsHelper;
use app\util\Tools;
use app\util\CosHelper;
use app\util\WechatHelper;
use think\facade\Cache;

class CommonController extends BaseController
{
    protected $checkLogin = false;

    public function test()
    {
        $file_path = $this->app->getRootPath()."public/logo.jpg";
        $res = CosHelper::upload($file_path);
        return json($res);
    }

    /**
     * 图片上传
     */
    public function upload()
    {
        if ($this->request->isPost()) {
            set_time_limit(0);
            $file = isset($_FILES["file"]) ? $_FILES["file"] : null;

            if (empty($file)) {
                return $this->outJson(100, '请选择要上传的图片');
            }
            $file_type = strtolower($file['type']);
            if (!in_array($file_type, ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'])) {
                return $this->outJson(100, '图片格式不正确，只允许jpg,jpeg,png或gif格式');
            }
            if ($file['size'] > 10 * 1024 * 1024) {
                return $this->outJson(100, '图片大小不能超过10Mb');
            }

            $ret = CosHelper::upload($file["tmp_name"],Tools::getExtension($file["name"]));
            return json($ret);
        }

        return $this->outJson(-1, "非法请求");
    }

    /**
     * 发送短信验证码
     */
    public function smsCode()
    {
        if(!$this->request->isPost()){
            return $this->outJson(500,"非法请求");
        }

        $mobile = $this->request->post("mobile",'',"trim");
        $type = $this->request->post("type",'login',"trim");

        if(empty($mobile)){
            return $this->outJson(100,"手机号不能为空");
        }

        $cache_key = SmsHelper::getCacheKey($mobile,$type);
        $vcode = Cache::get($cache_key);
        $mask_mobile = Tools::maskMobile($mobile);
        //判断是否已发送过
        if ($vcode) {
            return $this->outJson(0,"验证码已发送到{$mask_mobile}，请注意查收");
        }

        $vcode = mt_rand(1000,999999);
        $result = SmsHelper::sendSmsMessage($mobile,"【文影科技】短信验证码：{$vcode}，有效期5分钟");
        if ($result['code'] != 0) {
            return $this->outJson($result["code"],$result['msg']);
        }
        $expire = APP_ENV == "test" ? 2 * 3600 : 5 * 60; //5分钟有效
        Cache::set($cache_key,$vcode, $expire);

        return $this->outJson(0,"验证码已发送到{$mask_mobile}，请注意查收");
    }

    /**
     * 生成小程序二维码
     */
    public function genMiniQr()
    {
        $page = $this->request->param("page", '', "trim");
        $scene = $this->request->param("scene", '', "trim");
        $width =  $this->request->param("width", '430', "int");
        $accessToken = WechatHelper::getAccessToken();
        //var_dump($accessToken);
        $res = WechatHelper::getMiniQr($page, $scene, $width, $accessToken);
        //return $res;
        //return $this->outJson(0,"生成成功！",$res);
        return response($res, 200, ['Content-Length' => strlen($res)])->contentType('image/jpg');
    }
    /**
     * 返回logo图片地址
     * @return string
     */
    private function getSavePath($file_name)
    {
        $save_path = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "upload"; //保存目录
        if (!file_exists($save_path)) {
            @mkdir($save_path, 0755, true); //创建目录
        }
        return $save_path . DIRECTORY_SEPARATOR . date('YmdHis') . "_".$file_name;
    }
}
