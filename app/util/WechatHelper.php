<?php
/**
 */
namespace app\util;

use think\facade\Config;
use think\facade\Cache;
use think\facade\Db;

class WechatHelper
{

    public static function getWechatOpenId($code)
    {
        $wechat_config = Config::get('weixin');
        $realUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $wechat_config['appid'] . '&secret=' . $wechat_config['secret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $res = Tools::curlGet($realUrl, null);
        if ($res == null || !isset($res["openid"])) {
            return "";
        }
        return $res["openid"];
    }

    public static function getAccessToken()
    {
        $weiXin_config = Config::get('weixin');
        $app_id = $weiXin_config["appid"];
        $secret = $weiXin_config["secret"];

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $app_id . "&secret=" . $secret . "";
        $res = Tools::curlGet($url, null);
        if ($res == null || !isset($res["access_token"])) {
            return "";
        }
        return $res["access_token"];
    }

    public static function getMiniQr($page, $scene, $width, $access_token)
    {
        $data = array("path" => $page, "width" => $width);
        if (!empty($scene)) {
            $data = array("path" => $page, "width" => $width, "scene" => $scene);
        }
        $header = array();
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $res = Tools::curlPost($url, $data, true, $header, false);
        //$path = 'D:\h.jpg';
        //file_put_contents($path, $res);
        return $res;
    }
}
