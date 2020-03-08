<?php
/**
 * 检测直播是否在线
 */
namespace app\command;

use TencentCloud\Common\Credential;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamOnlineListRequest;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamOnlineListResponse;
use TencentCloud\Live\V20180801\LiveClient;
use app\model\TRoom;
use think\facade\Config;

class LiveCheckCommand extends BaseCommand
{
    /**
     * @var string 指令名称
     */
    protected $scriptName = "live_check";

    /**
     * 执行入口
     */
    protected function _execute()
    {
        while(true){
            if ($this->checkScriptStop()){
                $this->log("script stop");
                break;
            }

            $online_room_ids = $this->getOnlineRooms();

            $this->log("在线直播:".json_encode($online_room_ids));

            $not_online_list = TRoom::where("room_id","not in",$online_room_ids)->field("room_id,user_id")->select();

            $this->log("共".count($not_online_list)."个不在线直播");

            if(!empty($not_online_list)){
                $not_online_list = $not_online_list->toArray();
                foreach($not_online_list as $item){
                    $ret = TRoom::closeRoom($item["room_id"],$item["user_id"]);
                    $this->log("关闭直播:room_id:".$item["room_id"].",ret:".json_encode($ret,JSON_UNESCAPED_UNICODE));
                }

                sleep(15);
            }else{
                sleep(15);
            }
        }
        $this->log("done");
    }

    /**
     * @return array
     */
    public function getOnlineRooms()
    {
        // 实例化一个证书对象，入参需要传入腾讯云账户secretId，secretKey
        $conf = Config::get("tencent_clound");
        $cred = new Credential($conf["secretId"],$conf["secretKey"]);

        // # 实例化要请求产品(以cvm为例)的client对象
        $client = new LiveClient($cred, "");

        // 实例化一个请求对象
        $req = new DescribeLiveStreamOnlineListRequest();
        //$req->AppName = "live";
        $req->PageNum = 1;
        $req->PageSize = 100;

        $res = $client->DescribeLiveStreamOnlineList($req);

        $arr = $res->serialize();
        $OnlineInfo = $arr['OnlineInfo'] ?? [];

        $online_room_ids = [];
        foreach($OnlineInfo as $item){
            $arr = explode("_",$item['StreamName']); //1400319314_101062
            $room_id = "room_".$arr[1];
            $online_room_ids[] = $room_id;
        }

        return $online_room_ids;
    }
}