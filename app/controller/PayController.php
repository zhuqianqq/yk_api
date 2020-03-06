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
        Tools::addLog("aliapy_notify","支付宝回调开始:".$this->request->getInput());
        $aliPay = new AlipayService();
        //首先必需验证签名，然后验证是否是支付宝发来的通知。
        $verify_result = $aliPay->verifyNotify();

        if ($verify_result) {
            $result = $aliPay->alipayNotify($this->request->param());

            if ($result) {
                Tools::addLog("aliapy_notify","支付宝回调通知处理成功");
                exit("success"); //成功时返回success
            } else {
                Tools::addLog("aliapy_notify","支付宝回调通知处理失败");
                exit("failed");
            }
        } else {
            Tools::addLog("aliapy_notify","支付宝回调验证签名失败");
            exit("failed");
        }
    }
}
