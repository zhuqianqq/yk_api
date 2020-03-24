<?php
/**
 * 主播认证
 */

namespace app\model;

use app\util\AccessKeyHelper;
use app\util\TLSSigAPIv2;
use app\util\Tools;
use think\facade\Config;
use think\facade\Db;
use think\Validate;

class TMemberValidates extends BaseModel
{
    // 0 待审核 1 审核中 2 审核通过 3 审核失败
    const STATUS_INIT = 0;
    const STATUS_CHECKING = 1;
    const STATUS_PASS = 2;
    const STATUS_FAIL = 3;

    protected $table = "t_member_validate";

    protected $pk = "validateId";

    protected $field = true;


    /**
     * @var array 是否已实名
     */
    public static $AUDIT_STATUS_ARR = [
        "1" => "是",
        "0" => "否"
    ];

    /**
     * 新增
     */
    public function add(){
        $data = input('post.');
        $userid = isset($data['user_id']) ? $data['user_id'] : 0;
        $trueName = isset($data['true_name']) ? $data['true_name'] : '';
        $id_card = isset($data['id_card']) ? $data['id_card'] : '';
        $id_card_positive = isset($data['id_card_positive']) ? $data['id_card_positive'] : '';
        if (empty($userid) || empty($trueName) || empty($id_card) || empty($id_card_positive)) {
            throw new \Exception('缺少参数');
        }

        $user = TMember::where("user_id = {$userid}")->find();
        if (empty($user)) {
            throw new \Exception('没有该记录');
        }
        $userRecord = $this->where("user_id = {$userid}")->find();
        if (!empty($userRecord)) {
            throw new \Exception('请勿重复提交');
        }

        $validate = new validate();
        if(!$validate->scene('add')->check($data)) {
            throw new \Exception($validate->getError());
        };
        $insertData['user_id'] = $userid;
        $insertData['true_name'] = $trueName;
        $insertData['id_card'] = $id_card;
        $insertData['id_card_positive'] = $id_card_positive;
        $insertData['create_time'] = date('Y-m-d H:i:s');
        $id = $this->insertGetId($insertData);
        if(false !== $id){
            return $id;
        }
        throw new \Exception("新增失败");
    }
    /**
     * 编辑
     */
    public function edit($validateId){
        if (empty($validateId)) {
            throw new \Exception('缺少参数');
        }
        $data = input('post.');
        $userRecord = $this->where("validateId = {$validateId}")->find();
        if (empty($userRecord)) {
            throw new \Exception('请先添加');
        }
        $status = $userRecord['status'];
        // 如果状态为审核中和审核通过，则不能进行修改
        $noEditStatus = [self::STATUS_CHECKING, self::STATUS_PASS];
        if (in_array($status, $noEditStatus)) {
            throw new \Exception('当前不可编辑');
        }
        $updateData = [];
        if ($status == self::STATUS_FAIL) {
            // 如果是拒绝重新提交，则重置为0
            $updateData['status'] = self::STATUS_INIT;
            $updateData['remark'] = '';
        }

        if (empty($data['true_name'])) {
            $updateData['true_name'] = $userRecord['true_name'];
        } else {
            $updateData['true_name'] = $data['true_name'];
        }
        if (empty($data['id_card'])) {
            $updateData['id_card'] = $userRecord['id_card'];
        } else {
            $updateData['id_card'] = $data['id_card'];
        }

        if (empty($data['id_card_positive'])) {
            $updateData['id_card_positive'] = $userRecord['id_card_positive'];
        } else {
            $updateData['id_card_positive'] = $data['id_card_positive'];
        }

        $result = $this->where("validateId = {$validateId}")->save($updateData);
        if(false !==$result){
            if(false !== $result){
                return true;
            }
        }
        throw new \Exception("修改失败");
    }

    /**
     * 查询
     * @throws \Exception
     */
    public function pageQuery()
    {
        $data = input('post.');
        $userid = isset($data['user_id']) ? $data['user_id'] : 0;
        if (empty($userid)) {
            throw new \Exception('缺少参数');
        }
        $user = TMember::where("user_id = {$userid}")->find();
        if (empty($user)) {
            throw new \Exception('没有该记录');
        }
        $userRecord = $this->where("user_id = {$userid}")->find();
        if (empty($userRecord)) {
            throw new \Exception('没有数据');
        }
        switch ($userRecord['status']) {
            // 0 待审核 1 审核中 2 审核通过 3 审核失败
            case 1:
                $content = '等待审核中';
                break;
            case 2:
                $content = '恭喜您！审核通过';
                break;
            case 3:
                $content = '很遗憾，审核失败，具体原因如下:';
                break;
            default:
                $content = '待审核';
        }
        $data = [
            'validateId' => $userRecord['validateId'],
            'trueName' => $userRecord['true_name'],
            'idCard' => $userRecord['id_card'],
            'idCardPositive' => $userRecord['id_card_positive'],
            'status' => $userRecord['status'],
            'reason' => $userRecord['remark'],
            'tip' => $content,
        ];
        return $data;
    }
}
