<?php
/**
 * 检测直播是否在线，不在线的自动下播
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
            if($online_room_ids === false){
                sleep(2);
                continue;
            }
            $this->log("online_room_ids:".json_encode($online_room_ids));

            $not_online_list = TRoom::where("room_id","not in",$online_room_ids)->field("room_id,user_id")->select(); //不在线的直播
            $this->log("not_online_list count:".count($not_online_list));

            if(!empty($not_online_list)){
                $not_online_list = $not_online_list->toArray();
                foreach($not_online_list as &$item){
                    $ret = TRoom::closeRoom($item["room_id"],$item["user_id"],"system"); //系统自动下播
                    $this->log("close room_id:{$item['room_id']},user_id:{$item['user_id']},ret:".json_encode($ret,JSON_UNESCAPED_UNICODE));
                }
                unset($item);
            }
            $this->log(PHP_EOL);

            sleep(20);
        }
        $this->log("done");
    }

    /**
     * 获取在线直播列表
     * @return array|bool 失败时返回false
     */
    public function getOnlineRooms()
    {
        // 实例化一个证书对象，入参需要传入腾讯云账户secretId，secretKey
        $conf = Config::get("tencent_clound");
        $cred = new Credential($conf["secretId"],$conf["secretKey"]);

        //实例化要请求client对象
        $client = new LiveClient($cred, "");

        $online_room_ids = []; //所有在线room_ids
        $page_num = 1;
        $page_size = 100;
        try{
            do{
                $req = new DescribeLiveStreamOnlineListRequest(); // 实例化一个请求对象
                //$req->AppName = "live";
                $req->PageNum = $page_num; //取得第几页，默认1。
                $req->PageSize = $page_size;//每页大小，最大100。取值：10~100之间的任意整数。默认值：10。

                $res = $client->DescribeLiveStreamOnlineList($req);
                $arr = $res->serialize();
                if(empty($arr)){
                    usleep(200000);
                    continue;
                }
                //print_r($arr);
                $this->log("pagenum:{$page_num},page_size:{$page_size},res:\n".json_encode($arr,JSON_UNESCAPED_UNICODE));

                $total_page = $arr['TotalPage']; //总页数
                $OnlineInfo = $arr['OnlineInfo'] ?? []; //正在推送流的信息列表
                if($page_num >= $total_page || empty($OnlineInfo)){
                    break;
                }
                foreach($OnlineInfo as $item){
                    $arr = explode("_",$item['StreamName']); //1400319314_101062
                    $room_id = "room_".$arr[1];
                    $online_room_ids[] = $room_id;
                }
                $page_num ++;
            }while(true);

            return $online_room_ids;
        }catch (\Exception $ex){
            $this->log("getOnlineRooms error:".$ex->getMessage());
            return false;
        }
    }
}