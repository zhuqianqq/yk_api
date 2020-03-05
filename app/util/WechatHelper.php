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

}
