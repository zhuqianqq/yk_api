<?php
/**
 * 管理后台控制器基础类
 */

namespace app\controller;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\facade\Session;
use think\facade\View;
use app\util\Tools;

abstract class BaseController
{
    /**
     * @var int 每页记录条数
     */
    public static $pageSize = 20;

    /**
     * @var bool 是否检测登录
     */
    protected $checkLogin = false;

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 是否开启跨域 默认开启
     * @var bool
     */
    protected $cors = true;

    /**
     * @var 应用渠道
     */
    protected $channel;

    /**
     * @var 当前用户id
     */
    protected $user_id = 0;

    protected $logfile = '';

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        if ($this->cors) {
            //header("Access-Control-Allow-Origin:*");
//            header("Access-Control-Allow-Credentials: true");
//            header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
//            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With,user-id,access-key");
//            header("Content-Type: application/json; charset=utf-8");
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "OPTIONS") {
            header("HTTP/1.1 204 No Content"); //跨域options请求
            exit;
        }

        $user_id = $this->request->header('user_id') ?? $this->request->param('user_id');
        $this->user_id = intval($user_id);
        $this->channel = $this->request->header('channel', '');
    }

    /**
     * URL 重定向
     * @param string $url 跳转的 URL 表达式
     * @param array params 其它 URL 参数
     * @return void
     */
    protected function redirect($url, $params = [])
    {
        if (!empty($params)) {
            if (strpos($url, "?") !== false) {
                $url .= '?' . http_build_query($params);
            } else {
                $url .= '&' . http_build_query($params);
            }
        }
        header("Location: {$url}");
        exit();
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 输出json数组
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function outJson($code = 0, $msg = '', $data = [])
    {
        return json(Tools::outJson($code, $msg, $data));
    }
    protected function outJsonWithNullData($code = 0, $msg = '', $data = [])
    {
        return json(Tools::outJsonWithnullData($code, $msg, $data));
    }
    /**
     * 输出json数组
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function successJson($data = [],$code = 0, $msg = 'success')
    {
        return json(Tools::outJson($code, $msg, $data));
    }

    /**
     * 输出json数组
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function failJson($code = -1, $msg = '', $data = [])
    {
        return json(Tools::outJson($code, $msg, $data));
    }

    /**
     * 返回当前登录登录用户uid
     * @return int
     */
    protected function getCurrentUserId()
    {
        return $this->getCurrentUserId();
    }

    /**
     * @param $msg
     * @param $context
     */
    protected function log($msg,$context = [])
    {
        if($this->logfile){
            Tools::addLog($this->logfile,$msg,$context);
        }
    }
}
