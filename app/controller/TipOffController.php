<?php
namespace app\controller;

use app\model\TPrebroadcast;
use TencentCloud\Tics\V20181115\Models\TagType;
use think\facade\Db;
use app\model\TTipOff;

class TipOffController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['add']],
    ];
    /**
     * 提交一个举报
     * @return array
     */
    public function add()
    {
        $to_user_id = $this->request->param("to_user_id",0,"intval");
        $from_user_id = $this->request->param("from_user_id",0,"intval");
        $reason = $this->request->param("reason");
        $detail = $this->request->param("detail");
        $from_phone = $this->request->param("from_phone");
        if (!$from_phone) {
            return $this->outJson(100, "手机号码必需填写");
        }
        $img_list = $this->request->param("img_list");

        $nowTime = time();
        $time = date('Y-m-d H:i:s', $nowTime);
        $item = new TTipOff();
        $item->to_user_id = $to_user_id;
        $item->from_user_id = $from_user_id;
        $item->reason = $reason;
        $item->detail = $detail;
        $item->from_phone = $from_phone;
        $item->img_list = $img_list;
        $item->create_time = $time;
        $item->save();
        return $this->outJson(0, "保存成功！",$item);
    }
}
