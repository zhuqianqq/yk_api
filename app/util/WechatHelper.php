<?php
/**
 */
namespace app\util;

use think\facade\Cache;
use think\facade\Db;

class WechatHelper
{

    public static function getWechatOpenId($code)
    {
        $wechat_config = Config::get('weixin');
        $realUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $wechat_config['appid'] . '&secret=' . $wechat_config['secret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $res = json_decode(Tools::curlGet($realUrl));
        if ($res->openid == null) {
            return "";
        }
        return $res->openid;
    }

}
