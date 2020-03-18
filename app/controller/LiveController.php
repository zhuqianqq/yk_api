<?php
/**
 * 直播
 */

namespace app\controller;

use app\model\TMember;
use app\model\TPrebroadcast;
use app\model\TProduct;
use app\model\TProductRecommend;
use app\model\TRoom;
use app\model\TRoomHistory;
use app\model\TRoomOperLog;
use app\util\AccessKeyHelper;
use app\util\Tools;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\Model;

class LiveController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['closeRoom', 'addRoom', 'updateLikeAndView', 'preAddRoom']],
    ];

    /**
     * 首页直播列表
     */
    public function getList()
    {
        $page_size = $this->request->param("page_size", 10, "intval");
        $page = $this->request->param("page", 1, "intval");
        $page = $page ? $page : 1;
        $data = TRoom::getList($page, $page_size);

        return $this->outJson(0, "success", $data);
    }

    /**
     * 直播详情
     */
    public function getInfo()
    {
        $room_id = $this->request->param("room_id");
        $user_id = $this->request->param("user_id", 0, "intval");

        if ($room_id > 0) {
            $where = ['room_id' => $room_id];
        } else if ($user_id > 0) {
            $where = ['user_id' => $user_id];
        } else {
            return $this->outJson(100, "user_id或room_id无效");
        }

        $data = TRoom::where($where)->find();
        if ($data == null) {
            return $this->outJson(100, "找不到开播房间！");
        }
        $user = TMember::where("user_id", $data["user_id"])->find();
        if ($user != null) {
            $data["display_code"] = $user->display_code;
            $data["nick_name"] = $user->nick_name;
            $data["avatar"] = $user->avatar;
        } else {
            $data["display_code"] = "";
            $data["nick_name"] = "";
            $data["avatar"] = "";
        }
        return $this->outJson(0, "success", $data);
    }

    /**
     * 创建直播前确认是否可以开播
     */
    public function preAddRoom()
    {
        $user_id = $this->request->param("user_id", 0, "intval");
        if ($user_id <= 0) {
            return $this->outJson(1, "找不到对应账号！");
        }
        $user = TMember::where(["user_id" => $user_id])->find();
        if (empty($user)) {
            return $this->outJson(1, "找不到对应账号！");
        }
        if ($user->is_lock == 1) {
            AccessKeyHelper::generateAccessKey($user_id);
            return $this->outJson(200, "账号已被锁定");
        }

        if ($user->is_forbid == 1 && $user->forbid_end_time > date('Y-m-d H:i:s')) {
            return $this->outJson(300, "主播已禁播,原因：" + $user->forbid_reason);
        } else if ($user->is_forbid == 1) {
            TMember::unforbidMember($user_id);
        }
        return $this->outJson(0, "正常账号，可创建房间！");
    }

    /**
     * 创建直播
     */
    public function addRoom()
    {
        $user_id = $this->request->param("user_id", 0, "intval");
        $room_id = $this->request->param("room_id");
        $title = $this->request->param("title");
        $frontcover = $this->request->param("frontcover");
        $location = $this->request->param("location");

        $show_product = $this->request->param("show_product", 0, "intval");
        $prebroadcast_id = $this->request->param("prebroadcast_id", 0, "intval");
        $push_url = $this->request->param("push_url"); //推流地址
        $source_platform = $this->request->param("source_platform", 0, "intval"); //创建直播的平台

        //拉流地址
        $live_config = Config::get('tencent_cloud');
        $user = TMember::where(["user_id" => $user_id])->field("display_code")->find();
        $mixed_play_url = $live_config["pull_domain"] . "/live/" . $live_config["IM_SDKAPPID"] . "_" . $user->display_code . ".flv";

        //list($push_url,$pull_url) = TRoom::generatePushAndPUllUrl($user_id); //推流地址

        $room = TRoom::where(["user_id" => $user_id])->find();
        if ($room == null) {
            $room = new TRoom();
            $room->user_id = $user_id;
            $room->room_id = $room_id;
            $room->title = $title;
            $room->frontcover = $frontcover;
            $room->location = $location;
            $room->push_url = $push_url;
            $room->mixed_play_url = $mixed_play_url;
            $room->show_product = $show_product; //是否显示关联商品
            $room->create_time = date("Y-m-d H:i:s");
            $room->source_platform = $source_platform;
            $room->save();
        } else {
            TRoom::where([
                "user_id" => $user_id,
            ])->update([
                'title' => $title,
                'frontcover' => $frontcover,
                'location' => $location,
                'push_url' => $push_url,
                'mixed_play_url' => $mixed_play_url,
                'show_product' => $show_product,
                'source_platform' => $source_platform
            ]);
        }
        $room = TRoom::where(["user_id" => $user_id])->find();
        if ($prebroadcast_id > 0) {
            TPrebroadcast::where([
                "id" => $prebroadcast_id,
            ])->update([
                'status' => 1
            ]);
        }
        TProductRecommend::clearRecommends($user_id);
        Cache::store('redis')->lpush('list:wechat:subscribe', $user_id);
        return $this->outJson(0, "开播成功！", $room);
    }

    /**
     * 直播关播
     */
    public function closeRoom()
    {
        $room_id = $this->request->param("room_id", '', "trim");

        if (empty($room_id)) {
            return $this->outJson(100, "room_id不能为空");
        }

        return json(TRoom::closeRoom($room_id, $this->user_id, $this->user_id));
    }

    /**
     * 直播间主播商品列表
     * @return array
     */
    public function userProdList()
    {
        $page = $this->request->param("page", 1, "intval");
        $page_size = $this->request->param("page_size", 10, "intval");
        $room_id = $this->request->param("room_id", "", "trim");
        $user_id = $this->request->param("user_id", 0, "intval");  //主播id

        if (empty($room_id)) {
            return $this->outJson(100, "直播房间id不能为空");
        }
        if ($user_id <= 0) {
            return $this->outJson(100, "user_id无效");
        }

        $show_product = TRoom::where("room_id", $room_id)->value("show_product");
        if (!$show_product) { //不显示商品
            $data = [
                "list" => [],
                "current_page" => $page,
                "total" => 0,
                "has_next" => 0, //是否有下一页
            ];
            return $this->outJson(0, "主播不展示商品", $data);
        }

        $where["user_id"] = $user_id;
        $where["is_online"] = 1;

        list($list, $total, $has_next) = TProduct::getList($page, $page_size, $where, ["weight" => "desc"]);

        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];

        return $this->outJson(0, "success", $data);
    }

    /**
     * 直播点赞数和在线人数上报
     */
    public function updateLikeAndView()
    {
        $room_id = $this->request->param("room_id", '', "trim");
        $like_count = $this->request->param("like_count", 0, "intval");
        $view_count = $this->request->param("view_count", 0, "intval");

        if (empty($room_id)) {
            return $this->outJson(100, "room_id不能为空");
        }
        $room = TRoom::where("room_id", $room_id)->find();
        if ($room == null) {
            return $this->outJson(100, "找不到直播间，可能已经下线");
        }
        $room->like_count = $like_count;
        $room->view_count = $view_count;
        $room->save();

        return $this->outJson(0, "success");
    }

    /**
     * 腾讯直播回调通知
     * 直播推流事件，event_type = 1
     * 直播断流事件，event_type = 0
     * 录制事件为 100；截图事件为200
     * 建议客户应答内容携带 JSON： {"code":0}
     */
    public function tencentCallBack()
    {
        $input = $this->request->getInput();
        $data = json_decode($input, true);
        if (empty($data)) {
            Tools::addLog("live_callback", "参数错误", $input);
            return $this->outJson(100, "参数错误");
        }

        $event_type = $data['event_type']; //推流事件为1；断流事件为0；录制事件为100；截图事件为200。
        $check_t = $data['t']; //过期时间
        $check_sign = $data['sign']; //安全签名

        $live_config = Config::get("tencent_cloud");
        $md5_sign = $md5_val = md5($live_config["callback_key"] . strval($check_t));
        if ($md5_sign != $check_sign) {
            Tools::addLog("live_callback", "签名错误", $input);
            //return $this->outJson(100,"签名错误");
        }

        if ($event_type === 1) {
            //推流事件
            $app = $data['app']; //推流域名
            $appname = $data['appname']; //推流路径
            $stream_id = $data['stream_id']; //直播流名称
            $stream_param = $data['stream_param']; //用户推流 URL 所带参数
            $sequence = $data['sequence']; //消息序列号，标识一次推流活动，一次推流活动会产生相同序列号的推流和断流消息
            $display_code = explode("_", $stream_id)[1];
            $room_id = "room_" . $display_code;

            sleep(3);//延时一下再更新
            $ret = TRoom::where("room_id", $room_id)->update(["sequence" => $sequence]); //更新序列号
            Tools::addLog("live_callback", "update_sequence:{$room_id},{$sequence},ret:{$ret}", $input);

            if ($ret) {
                return $this->outJson(0, "success");
            } else {
                return $this->outJson(500, "update error");
            }
        } else if ($event_type === 0) {
            //断流事件
            $stream_id = $data['stream_id']; //直播流名称
            $display_code = explode("_", $stream_id)[1];
            $room_id = "room_" . $display_code;
            $user_id = TMember::getUserIdByDisplayCode($display_code);
            $sequence = $data['sequence']; //消息序列号，标识一次推流活动，一次推流活动会产生相同序列号的推流和断流消息

            //$ret = TRoom::closeRoomBySystem($room_id, $sequence, "system");
            //Tools::addLog("live_callback", "close_result:{$room_id},{$user_id},{$sequence}," . json_encode($ret, JSON_UNESCAPED_UNICODE), $input);

            //return json($ret);

            return $this->outJson(0, "success");
        } else if ($event_type === 100) {
            //录制事件为100
            $stream_id = $data['stream_id'];
            $video_id = $data['video_id'];
            $video_url = $data['video_url'];
            $start_time = $data['start_time'];
            $end_time = $data['end_time'];
            $file_format = $data['file_format'];

            $duration = $end_time - $start_time;
            if ($duration > 60) {//超过60秒的录制文件才会落数据库，单位s

            }

            Tools::addLog("live_callback", "success", $input);
            return $this->outJson(0, "success");
        } else {
            Tools::addLog("live_callback", "success", $input);
            return $this->outJson(0, "success");
        }
    }
}
