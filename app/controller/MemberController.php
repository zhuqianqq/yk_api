<?php
namespace app\controller;

use app\model\TMember;
use app\model\TPrebroadcast;
use app\util\AccessKeyHelper;
use think\facade\Db;

class MemberController extends BaseController
{

    public function updateMember()
    {
        $user_id = $this->request->post("user_id", '', "intval");
        $nick_name = $this->request->post("nick_name", '');
        $avatar = $this->request->post("avatar", '');
        $sex = $this->request->post("sex", '', "intval");
        $front_cover = $this->request->post("front_cover", '');
        $member = TMember::where("user_id", $user_id)->find();
        $member->nick_name = empty($nick_name) ? $member->nick_name : $nick_name;
        $member->avatar = empty($avatar) ? $member->avatar : $avatar;
        $member->sex = empty($sex) ? $member->sex : $sex;
        $member->front_cover = $front_cover;
        $member->save();
        return $this->outJson(0, "保存成功！");
    }

    public function memberDetail()
    {
        $user_id = $this->request->get("user_id", '', "intval");
        $member = TMember::where("user_id", $user_id)->field("nick_name,avatar,sex,front_cover,is_broadcaster")->find();
        if ($member == null) {
            return $this->outJson(1, "指定的用户不存在！");
        }
        return $this->outJson(0, "查找成功！", $member);
    }

    public function refreshMember()
    {
        $user_id = $this->request->get("user_id", '', "intval");
        $member = TMember::where("user_id", $user_id)->find();
        if ($member == null) {
            return $this->outJson(1, "指定的用户不存在！");
        }
        $data["user_id"] = $user_id;
        $data["access_key"] = AccessKeyHelper::generateAccessKey($user_id); //生成access_key
        return $this->outJson(0, "刷新用户会话标识成功！", $data);
    }
}
