<?php
/**
 * 腾讯云直播服务
 */

namespace app\service;

use think\facade\Config;
use app\util\Tools;

class TenCloudLiveService extends BaseService
{
    /**
     * @var string 日志名
     */
    protected $logName = "TenCloudLiveService";

    /**
     * @var string 您用来推流的域名
     */
    protected $domain;


    public function __construct()
    {
        $this->config = Config::get('tencent_cloud');
        $this->domain = $this->config['domain'];
    }


    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param domain 您用来推流的域名
     *        streamName 您用来区别不同推流地址的唯一流名称
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    public function getPushUrl($domain, $streamName, $key = null, $time = null)
    {
        if ($key && $time) {
            $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str = "?" . http_build_query(array(
                    "txSecret" => $txSecret,
                    "txTime" => $txTime
                ));
        }
        return "rtmp://" . $domain . "/live/" . $streamName . (isset($ext_str) ? $ext_str : "");
    }
}