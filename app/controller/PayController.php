<?php
/**
 * 支付回调
 */

namespace app\controller;

use app\model\TUserPay;
use think\facade\Config;
use think\facade\Db;
use think\facade\Cache;
use app\util\Tools;
use app\service\AlipayService;

class PayController extends BaseController
{
    /**
     * 支付宝回调通知
     * https://docs.open.alipay.com/59/103666/
     */
    public function alipayNotify()
    {
        Tools::addLog("aliapy_notify", "支付宝回调开始:" . $this->request->getInput());
        $aliPay = new AlipayService();
        //首先必需验证签名，然后验证是否是支付宝发来的通知。
        $verify_result = $aliPay->verifyNotify();

        if ($verify_result) {
            $result = $aliPay->alipayNotify($this->request->param());

            if ($result) {
                Tools::addLog("aliapy_notify", "支付宝回调通知处理成功");
                exit("success"); //成功时返回success
            } else {
                Tools::addLog("aliapy_notify", "支付宝回调通知处理失败");
                exit("failed");
            }
        } else {
            Tools::addLog("aliapy_notify", "支付宝回调验证签名失败");
            exit("failed");
        }
    }

    /*
     * 创建邀请订单
     */
    public function createInviteOrder()
    {
        try {
            Db::startTrans();

            $userPay = new UserPay();
            $userPay->invite_order_id = $invite_order_id;
            $userPay->user_id = $this->request_data['user_id'];

            $userPay->save();

            Db::commit();
            $data = [
                'invite_order_id' => $invite_order_id, //邀请订单号
                'pay_amount' => $pay_amount, //要支付的金额
            ];
            return $this->outJson(0, "success", $data);

        } catch (\Exception $ex) {
            Db::rollback();
            Tools::addLog("invite_order", "error:" . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString(), $this->request->getInput());
            return $this->outJson(500, "接口异常:" . $ex->getMessage());
        }
    }

    /*
     * 邀请订单去付款-生成第三方支付请求签名包
     */
    public function submitInviteOrderPay()
    {
        try {
            $user_id = $this->request->post("user_id", 0, "intval"); //用户id
            $invite_order_id = $this->request->post("invite_order_id", 0, "intval"); //业务订单号
            $amount = $this->request->post("amount", 0, "floatval");  //支付金额
            $return_url = $this->request->post("return_url", '', "trim"); //支付成功回跳页面地址

            if ($user_id <= 0) {
                return $this->outJson(100, "user_id不能为空");
            }
            if ($invite_order_id <= 0) {
                return $this->outJson(100, "业务订单号不能为空");
            }
            if ($amount <= 0) {
                return $this->outJson(100, "支付金额不能为空");
            }

            Db::startTrans();
            $userPay = TUserPay::where('invite_order_id', $invite_order_id)->where("pay_status", 0)->find();
            if (empty($userPay)) {
                return $this->outJson(100, "未找到待支付订单");
            }

            $data = array();
            $data['user_id'] = $user_id;
            $data['user_pay_id'] = $userPay->user_pay_id;
            $data['amount'] = $amount;
            $data['subject'] = '映购主播开通付款';
            $data['return_url'] = $return_url;

            $res = (new AlipayService())->wapPay($data);
            Db::commit();

            return $this->outJson(0, "success", $res);
        } catch (\Exception $ex) {
            Db::rollback();
            Tools::addLog("invite_order", "error:" . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString(), $this->request->getInput());
            return $this->outJson(500, "接口异常:" . $ex->getMessage());
        }
    }
}
