<?php
namespace app\controller;

use app\model\TMember;
use app\model\TMemberValidates;
use app\model\TPrebroadcast;
use app\util\AccessKeyHelper;
use think\facade\Db;

class MemberController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['updateMember','refreshMember']],
    ];

    /*
     * 更新用户信息
     */
    public function updateMember()
    {
        $user_id = $this->request->param("user_id", '', "intval");
        $nick_name = $this->request->post("nick_name", '');
        $avatar = $this->request->post("avatar", '');
        $sex = $this->request->post("sex", '', "intval");
        $front_cover = $this->request->post("front_cover", '');
        if(mb_strlen($nick_name,"utf-8")>8){
            $this->outJson(1, "昵称限制8个字符！");
        }
        $member = TMember::where("user_id", $user_id)->find();
        $member->nick_name = empty($nick_name) ? $member->nick_name : $nick_name;
        $member->avatar = empty($avatar) ? $member->avatar : $avatar;
        $member->sex = empty($sex) ? $member->sex : $sex;
        $member->front_cover = $front_cover;
        $member->is_update_info = 1;
        $member->save();
        return $this->outJson(0, "保存成功！");
    }

    /*
     * 用户信息详情
     */
    public function memberDetail()
    {
        $user_id = $this->request->get("user_id", '', "intval");
        $member = TMember::where("user_id", $user_id)->field("nick_name,avatar,sex,front_cover,is_broadcaster,phone")->find();
        if ($member == null) {
            return $this->outJson(1, "指定的用户不存在！");
        }
        return $this->outJson(0, "查找成功！", $member);
    }

    /*
     * 刷新用户会话标识
     */
    public function refreshMember()
    {
        $user_id = $this->request->get("user_id", '', "intval");
        $member = TMember::where("user_id", $user_id)->find();
        if ($member == null) {
            return $this->outJson(1, "指定的用户不存在！");
        }
        $data["user_id"] = $user_id;
        $data["display_code"] = $member->display_code;
        TMember::setOtherInfo($data);
        //$data["access_key"] = AccessKeyHelper::generateAccessKey($user_id); //生成access_key
        return $this->outJson(0, "刷新用户会话标识成功！", $data);
    }

    /**
     * 实名认证-添加
     * @return array
     */
    public function userValidate()
    {
        try {
            $type = input('param.type/d'); // 类型 1 添加 2 修改
            if ($type == 2) {
                return $this->userValidateEdit();
            }
            $m = new TMemberValidates();
            $id = $m->add();
            return $this->outJson(0, "success", ['validateId' => $id]);
        } catch (\Exception $e) {
            return $this->outJson(100, $e->getMessage());
        }
    }

    /**
     * 实名认证-修改
     * @return array
     */
    public function userValidateEdit()
    {
        try {
            $validateId = input('param.validateId/d'); // 认证ID
            $m = new TMemberValidates();
            $m->edit($validateId);
            return $this->outJson(0, "success");
        } catch (\Exception $e) {
            return $this->outJson(100, $e->getMessage());
        }
    }

    /**
     * 详情
     */
    public function userValidateDetail()
    {
        try {
            $m = new TMemberValidates();
            $data = $m->pageQuery();
            return $this->outJson(0, "success", $data);
        } catch (\Exception $e) {
            return $this->outJson(100, $e->getMessage());
        }
    }

    /**
     * 实名认证-IOS 安卓同步
     */
    public function userAuditStatus()
    {
        try {
            $userid = input('param.user_id/d'); // 用户ID
            $audit_status = input('param.audit_status/d'); // 是否已实名1是，0不是
            $m = new TMember();
            $member = $m::where("user_id", $userid)->find();
            if ($member == null) {
                return $this->outJson(1, "指定的用户不存在！");
            }
            $audit_status_arr = [0, 1];
            if (!in_array($audit_status, $audit_status_arr)) {
                $audit_status = 0;
            }
            $member->audit_status = $audit_status;
            $rs = $member->save();
            if (false !== $rs) {
                return $this->outJson(0, "success");
            }

            return $this->outJson(1, "修改失败！");
        } catch (\Exception $e) {
            return $this->outJson(100, $e->getMessage());
        }
    }
}
