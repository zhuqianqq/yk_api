<?php
/**
 * 支付回调
 */

namespace app\controller;

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
        Tools::addLog("aliapy_notify", "支付宝回调开始,praram:" . $this->request->getInput());
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
     * 支付宝支付-生成第三方支付请求签名包
     */
    public function aliPay()
    {
        try {
            $user_id = $this->request->post("user_id", 0, "intval"); //用户id
            $order_num = $this->request->post("order_num",'', "trim"); //业务订单号
            $subject = $this->request->post("subject",'', "trim"); //订单标题
            $amount = $this->request->post("amount", 0, "floatval");  //支付金额
            $return_url = $this->request->post("return_url", '', "trim"); //支付成功回跳页面地址

            if ($user_id <= 0) {
                return $this->outJson(100, "user_id不能为空");
            }
            if (empty($order_num)) {
                return $this->outJson(100, "业务订单号不能为空");
            }
            if ($amount <= 0) {
                return $this->outJson(100, "支付金额不能为空");
            }

            $data = array();
            $data['user_id'] = $user_id;
            $data['order_num'] = $order_num;
            $data['amount'] = $amount;
            $data['subject'] = !empty($subject) ? $subject : '映购主播开通付款';
            $data['return_url'] = $return_url;

            $res = (new AlipayService())->wapPay($data);

            Tools::addLog("ali_pay","param:".json_encode($data,JSON_UNESCAPED_UNICODE).PHP_EOL."res:".json_encode($res));

            return json($res);
        } catch (\Exception $ex) {
            Tools::addLog("ali_pay", "error:" . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString(), $this->request->getInput());
            return $this->outJson(500, "接口异常:" . $ex->getMessage());
        }
    }
}
