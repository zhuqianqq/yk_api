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
     * @var 入口文件url
     */
    protected $entranceUrl;

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
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        $this->entranceUrl = $this->request->baseFile();
        if ($this->checkLogin && !$this->isLogin()) {
            if($this->request->isAjax()){
                $ret = $this->outJson(9999,'登录会话超时，请退出重新登录',["url" => $this->entranceUrl . '/login/index']);
                exit(json_encode($ret,JSON_UNESCAPED_UNICODE));
            }else{
                $this->redirect($this->entranceUrl . '/login');
            }
        }

        $lang = app()->lang->getLangSet();
        View::assign([
            'entranceUrl' => $this->entranceUrl, //入口文件
            'g_lang' => $lang,
        ]);
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
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
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
            $v     = new $class();
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
        return Tools::outJson($code,$msg,$data);
    }

    /**
     * 返回当前登录登录用户名
     * @return string
     */
    protected function getCurrentUserName()
    {
        $cur_user = Session::get('admin');
        return empty($cur_user) ? '' : $cur_user["username"];
    }

    /**
     * 返回当前登录登录用户uid
     * @return int
     */
    protected function getCurrentUserId()
    {
        $cur_user = Session::get('admin');
        return empty($cur_user) ? '' : $cur_user["uid"];
    }

    /**
     * 判断用户是否已经登录
     * @return bool
     */
    protected function isLogin()
    {
        return Session::has('admin');
    }
}
