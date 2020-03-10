<?php
/**
 * 腾讯云直播服务
 */

namespace app\service;

use think\facade\Config;
use app\util\Tools;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Live\V20180801\LiveClient;
use TencentCloud\Live\V20180801\Models\DropLiveStreamRequest;


class TenCloudLiveService extends BaseService
{
    /**
     * @var mixed sdk配置参数
     */
    protected $config;

    /**
     * @var string 日志名
     */
    protected $logName = "ten_cloud_live";

    /**
     * @var string 您用来推流的域名
     */
    protected $push_domain;

    public function __construct()
    {
        $this->config = Config::get('tencent_cloud');
        $this->push_domain = $this->config['push_domain'];
    }

    /**
     * @return Credential
     */
    public function getCredential()
    {
        return new Credential($this->config["secretId"], $this->config["secretKey"]);
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
    public function getPushUrl($stream_name, $key = null, $time = null)
    {
        if ($key && $time) {
            $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
            $txSecret = md5($key . $stream_name . $txTime);
            $ext_str = "?" . http_build_query(array(
                    "txSecret" => $txSecret,
                    "txTime" => $txTime
                ));
        }
        return $this->push_domain . "/live/" . $stream_name . (isset($ext_str) ? $ext_str : "");
    }

    /**
     * 断开直播流  https://cloud.tencent.com/document/api/267/20469
     * @param string $streamName 流名称
     * @param string $appName 推流路径，与推流和播放地址中的AppName保持一致，默认为 live。
     * @param string $domain 您的加速域名
     * https://live.tencentcloudapi.com/?Action=DropLiveStream&DomainName=5000.livepush.myqcloud.com
     * &AppName=live&StreamName=stream1&<公共请求参数>
     */
    public function dropLiveStream($streamName, $appName = 'live',$domain = '')
    {
        $cred = $this->getCredential();
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint("live.tencentcloudapi.com");

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new LiveClient($cred, "", $clientProfile);

        $req = new DropLiveStreamRequest(); //请求类
        $req->StreamName = $streamName;
        $req->DomainName = !empty($domain) ? $domain : $this->config['push_domain_cdn'];  //您的加速域名
        $req->AppName = $appName;

        $resp = $client->DropLiveStream($req);
        $this->log("droplive streamName:$streamName,domain:{$domain},appname:{$appName},res:" . $resp->toJsonString());

        $res = $resp->serialize();

        if(isset($res["Error"])){ //Error 的出现代表着该请求调用失败
            return Tools::outJson(500,"调用接口失败 code:".$res["Error"]["Code"]);
        }
        return Tools::outJson(0,"下播成功");
    }

    /**
     * 解析domain,appname,stream_name
     * @param $push_url
     * rtmp://push.laotouge.cn/live/1400319314_101162?txSecret=702052ac8d2c3633cf53d43fb6bcbbc6&txTime=5E685262
     */
    public static function parsePushUrl($push_url)
    {
        if(empty($push_url)){
            return '';
        }
        $arr = explode("?",$push_url);
        $arr2 = explode("/",str_replace('rtmp://','',$arr[0]));

        return $arr2;
    }
}