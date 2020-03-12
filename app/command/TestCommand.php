<?php
namespace app\command;

use think\facade\Config;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Live\V20180801\LiveClient;
use TencentCloud\Live\V20180801\Models\DescribePullStreamConfigsRequest;

use app\model\TMember;
use app\model\shop\DscUser;

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

//        $conf = Config::get("tencent_cloud");
//        $cred = new Credential($conf["secretId"],$conf["secretKey"]);
//
//        $httpProfile = new HttpProfile();
//        $httpProfile->setEndpoint("live.tencentcloudapi.com");
//
//        $clientProfile = new ClientProfile();
//        $clientProfile->setHttpProfile($httpProfile);
//        $client = new LiveClient($cred, "", $clientProfile);
//
//        $req = new DescribePullStreamConfigsRequest();
//
//        //$params = '{}';
//        //$req->fromJsonString($params);
//
//        $resp = $client->DescribePullStreamConfigs($req);
//
//        print_r($resp->toJsonString());

        $list = TMember::select()->toArray();

        foreach($list as $item){
            $shop_user_id = DscUser::register($item); //注册商城用户
            echo $shop_user_id.PHP_EOL;
        }
    }
}