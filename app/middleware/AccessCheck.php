<?php
/**
 * AccessKey 中间件
 */
namespace app\middleware;

use app\util\AccessKeyHelper;
use app\util\Tools;

class AccessCheck
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $user_id = intval($request->header('user-id') ?? $request->param('user_id'));
        $access_key = $request->header('access-key','');

        if($user_id <= 0 || empty($access_key)){
            return json(Tools::outJson(9001,"缺少access-key和user-id请求头"));
        }

        $check = AccessKeyHelper::validateAccessKey($user_id,$access_key);
        if(!$check){
            return json(Tools::outJson(9002,"access-key无效，请重新登录"));
        }

        $request->user_id = $user_id;
        $request->access_key = $access_key;

        return $next($request);
    }
}
