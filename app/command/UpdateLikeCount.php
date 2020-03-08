<?php
/**
 * 更新直播点赞数
 * 每15秒从redis中同步点赞数到db
 */
namespace app\command;

use app\model\TRoom;
use think\facade\Cache;

class UpdateLikeCount extends BaseCommand
{
    /**
     * @var string 指令名称
     */
    protected $scriptName = "update_like_count";

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
            $list = TRoom::field("room_id")->select()->toArray();
            foreach($list as $row){
                $room_id = $row['room_id'];
                $cache_key = "{$room_id}:like_count";
                $like_count = intval(Cache::get($cache_key));

                $this->log("update room_id:{$room_id},like_count:{$like_count}");
                if($like_count > 0){
                    TRoom::where("room_id",$room_id)->update(["like_count" => $like_count]);
                }
            }
            sleep(15);
        }
        $this->log("done");
    }
}