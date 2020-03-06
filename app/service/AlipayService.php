<?php
/**
 * 支付宝支付服务类(新接口方式)
 * 参考代码：
 * https://github.com/dcloudio/H5P.Server/tree/master/payment/alipayrsa2
 */

namespace app\service;

require_once 'alipay2/aop/AopClient.php';

use Exception;
use AopClient;
use AlipayTradeAppPayRequest;
use AlipayTradeRefundRequest;
use AlipayTradeWapPayRequest;
use think\facade\Config;
use app\util\Tools;
use app\model\TAlipayMobilePay;
use think\facade\Db;
use App\Models\Cfund\UserPay;

class AlipayService
{
    /**
     * @var string 日志名
     */
    protected $logName = "AlipayService";

    /**
     * HTTPS形式消息验证地址(移动支付请求支付宝的网关地址)
     */
    private $https_verify_url = 'https://mapi.alipay.com/gateway.do?';

    /**
     * HTTP形式消息验证地址
     * 老版本通过notify.alipay.com网关验证notify_id的方式将于2017年12月前计划下线
     */
    private $http_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';

    /**
     * @var array 支付配置
     */
    private $alipay_config;


    public function __construct()
    {
        $this->alipay_config = Config::get('alipay');
        print_r($this->alipay_config);
    }

    /**
     * @return AopClient
     */
    public function getAopClient()
    {
        $aop = new AopClient ();
        $aop->appId = $this->alipay_config["app_id"];
        $aop->signType = $this->alipay_config["sign_type"];
        $aop->rsaPrivateKey = $this->getRsaPrivateKey();
        $aop->alipayrsaPublicKey = $this->getAlipayPublicKey();

        return $aop;
    }

    /**
     * 生成APP支付付款请求参数 https://docs.open.alipay.com/204/106541
     * @param array $map 请求参数
     * @return bool
     */
    public function pay($map)
    {
        $user_id = $map['user_id'];
        $order_num = $map['order_num']; //用户业务订单号
        $amount = floatval($map['amount']);  //付款金额（元）
        $subject = $map['subject'] ?? ''; //商品描述字符串

        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        require_once 'alipay2/aop/request/AlipayTradeAppPayRequest.php';

        $request = new AlipayTradeAppPayRequest();
        $it_b_pay = "30m"; //30分钟

        // 生成alipay_mobile_pay订单
        $alipayMobilePay = TAlipayMobilePay::where('out_trade_no', $order_num)->find();
        if (empty($alipayMobilePay)) {
            $alipayMobilePay = new TAlipayMobilePay();
            $alipayMobilePay->out_trade_no = $order_num; //业务订单号
            $alipayMobilePay->app_id = $this->alipay_config['app_id']; //应用id
            $alipayMobilePay->partner = $this->alipay_config['partner'] ?? '';
            $alipayMobilePay->input_charset = $this->alipay_config["input_charset"];
            $alipayMobilePay->req_sign_type = $this->alipay_config["sign_type"] ?? '';
            $alipayMobilePay->notify_url = $this->alipay_config['notify_url'];  //支付回调通知地址
            $alipayMobilePay->subject = $subject; //商品描述字符串
            $alipayMobilePay->payment_type = 1; //支付类型。默认值为：1（商品购买）。
            $alipayMobilePay->seller_id = $this->alipay_config['seller_id']; //卖家支付宝账号
            $alipayMobilePay->total_fee = $amount; //付款金额（元）
            $alipayMobilePay->body = $subject;
            $alipayMobilePay->it_b_pay = $it_b_pay; //该笔订单允许的最晚付款时间
        }
        $alipayMobilePay->service = $request->getApiMethodName();  //app支付请求
        $alipayMobilePay->save();

        $aop = $this->getAopClient();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"" . $subject . "\","
            . "\"subject\": \"" . $subject . "\","
            . "\"out_trade_no\": \"" . $order_num . "\","
            . "\"timeout_express\": \"" . $it_b_pay . "\","
            . "\"total_amount\": \"" . $amount . "\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";  //product_code销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        $notify_url = urlencode($this->alipay_config['notify_url']);  // 异步通知地址
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);
        $sign_body = $aop->sdkExecute($request);

        $info = [
            "user_id" => $user_id,
            "order_num" => $user_pay_id, //业务订单号
            "sign_body" => $sign_body,  //该参数会提交给hbuilder的plus5+支付接口
        ];

        return $info;
    }

    /**
     * 生成支付宝移动支付跳转form表单的html（包含自动提交脚本）
     * https://docs.open.alipay.com/203/107090/
     * @param array $map
     * @return array
     */
    public function wapPay($map)
    {
        $user_id = $map['user_id'];
        $order_num = $map['order_num']; //订单编号
        $amount = floatval($map['amount']);  //付款金额（元）
        $subject = $map['subject'] ?? ''; //订单标题

        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        require_once 'Alipay2/aop/request/AlipayTradeWapPayRequest.php';
        $request = new AlipayTradeWapPayRequest();
        $it_b_pay = "30m"; //30分钟

        // 生成alipay_mobile_pay订单
        $alipayMobilePay = TAlipayMobilePay::where('out_trade_no', $order_num)->find();

        if (empty($alipayMobilePay)) {
            $alipayMobilePay = new TAlipayMobilePay();
            $alipayMobilePay->out_trade_no = $order_num;
            $alipayMobilePay->app_id = $this->alipay_config['app_id'];
            $alipayMobilePay->partner = $this->alipay_config['partner'];
            $alipayMobilePay->input_charset = $this->alipay_config["input_charset"];;
            $alipayMobilePay->req_sign_type = $this->alipay_config["sign_type"];
            $alipayMobilePay->notify_url = $this->alipay_config['notify_url'];
            $alipayMobilePay->subject = $subject;
            $alipayMobilePay->payment_type = 1; //支付类型。默认值为：1（商品购买）。
            $alipayMobilePay->seller_id = $this->alipay_config['seller_id'];
            $alipayMobilePay->total_fee = $amount;
            $alipayMobilePay->it_b_pay = $it_b_pay; //该笔订单允许的最晚付款时间
            $alipayMobilePay->save();
        }
        $alipayMobilePay->service = $request->getApiMethodName();//wap支付
        $alipayMobilePay->save();

        $aop = $this->getAopClient();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = "{\"body\":\"" . $subject . "\","
            . "\"subject\": \"" . $subject . "\","
            . "\"out_trade_no\": \"" . $order_num . "\","
            . "\"timeout_express\": \"" . $it_b_pay . "\","
            . "\"total_amount\": " . $amount . ","
            . "\"product_code\":\"QUICK_WAP_WAY\""
            . "}";  //product_code销售产品码，商家和支付宝签约的产品码，为固定值QUICK_WAP_WAY
        $notify_url = urlencode($this->alipay_config['notify_url']);  // 异步通知地址
        $request->setNotifyUrl($notify_url);
        $request->setBizContent($bizcontent);

        if (isset($map["return_url"]) && !empty($map["return_url"])) {
            $request->setReturnUrl($map["return_url"]);  //支付成功回跳页面
        }

        $sign_body = $aop->pageExecute($request, "GET");

        $info = [
            "user_id" => $user_id,
            "order_num" => $order_num, //业务订单号
            "sign_body" => $sign_body,  //前台回跳支付页面地址
        ];

        return $info;
    }

    /**
     * @return string 读取rsa私钥文件内容,去头去尾去回车，一行字符串
     */
    public function getRsaPrivateKey()
    {
        $key_path = config_path() . '/' . $this->alipay_config["private_key_path"];
        return file_get_contents($key_path);
    }

    /**
     * @return string 读取支付宝公钥文件内容,去头去尾去回车，一行字符串
     */
    public function getAlipayPublicKey()
    {
        $key_path = config_path() . '/' . $this->alipay_config["ali_public_key_path"];
        return file_get_contents($key_path);
    }

    /**
     * 支付宝异步回调通知，支付宝是用POST方式发送通知信息，
     * 程序执行完后必须打印输出“success”（不包含引号）。如果商户反馈给支付宝的字符不是success这7个字符，
     * 支付宝服务器会不断重发通知，直到超过24小时22分钟。
     * 一般情况下，25小时以内完成8次通知（通知的间隔频率一般是：4m,10m,10m,1h,2h,6h,15h）；
     * https://docs.open.alipay.com/59/103666/
     * @param array $map 通知参数
     * @return bool 成功返回true/false
     */
    public function alipayNotify($map)
    {
        try {
            $order_num = $map['out_trade_no'] ?? ''; // 商户网站唯一订单号
            if (empty($order_num)) {
                $this->log('alipayNotify: 缺失out_trade_no参数');
                return false;
            }

            $where = [
                "out_trade_no" => $order_num,
            ];
            // 更新alipay_mobile_pay订单状态
            $alipay_data = Db::table('t_alipay_mobile_pay')->where($where)->find();
            if (empty($alipay_data)) {
                $this->log('alipayNotify: 没有out_trade_no=' . $order_num . '支付宝回调记录');
                return false;
            }
            if ($alipay_data["notify_trade_status"] == 'TRADE_SUCCESS') {
                $this->log('alipayNotify: 交易状态已经为成功状态');
                return true;
            }

            $amount = @$map['total_amount']; // 交易金额
            Db::startTrans();
            $up_data = [
                "notify_time" => @$map['notify_time'],// 通知时间
                "notify_type" => @$map['notify_type'],// 通知类型
                "notify_id" => @$map['notify_id'],// 通知ID
                "notify_sign_type" => @$map['sign_type'],// 签名方式
                "notify_sign" => @$map['sign'],// 签名
                "notify_out_trade_no" => $order_num,// 业务订单号
                "notify_subject" => @$map['subject'],// 商品名称
                "notify_payment_type" => @$map['payment_type'],// 支付类型
                "notify_trade_no" => @$map['trade_no'],// 支付宝交易号
                "notify_trade_status" => @$map['trade_status'],// 交易状态
                "notify_seller_id" => @$map['seller_id'],// 卖家支付宝用户号
                "notify_seller_email" => @$map['seller_email'],// 卖家支付宝账号
                "notify_buyer_id" => @$map['buyer_id'],// 买家支付宝用户号
                "notify_buyer_email" => @$map['buyer_email'],// 买家支付宝账号
                "notify_total_fee" => $amount,
                "notify_quantity" => @$map['quantity'], // 购买数量,
                "notify_price" => @$map['price'], // 商品单价
                "notify_body" => @$map['body'], // 商品描述
                "notify_gmt_create" => @$map['gmt_create'], // 交易创建时间
                "notify_gmt_payment" => @$map['gmt_payment'], // 交易付款时间
                "notify_is_total_fee_adjust" => @$map['is_total_fee_adjust'], // 是否调整总价
                "notify_use_coupon" => @$map['use_coupon'], // 是否使用红包买家
                "notify_discount" => @$map['discount'], // 折扣
                "notice_time" => date('Y-m-d H:i:s'), // 收到通知时间
                "notify_is_total_fee_adjust" => @$map['is_total_fee_adjust'], // 是否调整总价
            ];

            if ($map['trade_status'] == 'TRADE_SUCCESS') {
                $up_data["finish_time"] = date('Y-m-d H:i:s'); // 收到支付宝完成通知时间
            }

            Db::table('t_alipay_mobile_pay')->where($where)->update($up_data);

            if ($map['trade_status'] == 'TRADE_SUCCESS') {
                // 更新user_pay订单状态
//                $userPay = UserPay::find($order_num);
//                $userPay->pay_status = 1; //支付成功
//                $userPay->pay_method = UserPay::PAY_METHOD_ALIPAY; //0：支付宝；1：微信；2：线下付款；3：新浪支付
//                $userPay->arrived_account_money = $amount;
//                $userPay->finish_payment_time = date('Y-m-d H:i:s');
//                $userPay->save();
//
//                $userPay->afterPaySuccess();
            }
            $this->log('支付宝回调通知success');

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            $this->log('支付宝回调通知消息：系统故障:' . $e->getMessage() . PHP_EOL . $e->getTraceAsString(), $map);
            return false;
        }
    }

    /**
     * 针对notify_url验证消息是否是支付宝发出的合法消息
     * https://docs.open.alipay.com/58/103597
     * @return 验证结果
     */
    public function verifyNotify()
    {
        if (empty($_POST)) {
            return false;
        } else {
            $aop = new AopClient();
            $aop->alipayrsaPublicKey = $this->getAlipayPublicKey();
            $flag = $aop->rsaCheckV1($_POST, NULL, $this->alipay_config["sign_type"]);
            return $flag;
        }
    }


    /**
     * @param $msg
     */
    public function log($msg)
    {
        Tools::addLog($this->logName, $msg);
    }
}