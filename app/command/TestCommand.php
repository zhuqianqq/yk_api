<?php
namespace app\command;

use think\facade\Config;
use TencentCloud\Common\Credential;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamOnlineListRequest;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamOnlineListResponse;
use TencentCloud\Live\V20180801\LiveClient;

class TestCommand extends BaseCommand
{
    /**
     * @var string 指令名称
     */
    protected $scriptName = "test";

    /**
     * 执行入口(处理业务逻辑)
     */
    protected function _execute()
    {
        $this->output->writeln("This is test command");

        $app_id = "1257835755";
        $secretId = "AKIDkMlvYwQye5KNCrwYhhz47OSKz0td5lEM";
        $secretKey = "0mz0lGVWJvHNJ0UigQvA8sMaaLq7IbNu";

        $im = Config::get("im");

        // 实例化一个证书对象，入参需要传入腾讯云账户secretId，secretKey
        $cred = new Credential($secretId,$secretKey);

        // # 实例化要请求产品(以cvm为例)的client对象
        $client = new LiveClient($cred, "");

        // 实例化一个请求对象
        $req = new DescribeLiveStreamOnlineListRequest();

        // 通过client对象调用想要访问的接口，需要传入请求对象
//        $resp = $client->DescribeZones($req);
//        print_r($resp->toJsonString());

          //实例化一个请求对象
        $req = new DescribeLiveStreamOnlineListRequest();
        //$req->AppName = "live";
        $req->PageNum = 1;
        $req->PageSize = 10;
        //$req->StreamName = "live";
        //$req->AppName = "live";

        $res = $client->DescribeLiveStreamOnlineList($req);

        print_r($res->toJsonString());
    }
}