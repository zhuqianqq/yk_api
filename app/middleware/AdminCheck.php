<?php
/**
 * AdminCheck 中间件（运营后台http://testboss.wengyingwangluo.cn调用接口的中间件)
 */
namespace app\middleware;

use app\util\AccessKeyHelper;
use app\util\Tools;

class AdminCheck
{
    /**
     * 密钥
     */
    const SIGN_KEY = "yinggou@123456";

    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $sign = $request->param('sign','','trim');
        $t = $request->param('t','','trim'); //时间戳

        if(empty($sign) || empty($t)){
            return json(Tools::outJson(9001,"缺少签名参数"));
        }

        $check_sign = md5($t.self::SIGN_KEY);
        if($check_sign != $sign){
            return json(Tools::outJson(9002,"签名参数非法"));
        }

        return $next($request);
    }
}
