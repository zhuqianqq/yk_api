<?php
/**
 * 工具类
 */
namespace app\util;

class Tools
{
    /**
     * 记录业务日志
     * @param string $file 日志文件名称(不带后缀)
     * @param string $msg 日志内容
     */
    public static function addLog($file, $msg)
    {
        $content = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        $log_name = $file . "_" . date('Ymd') . ".log";
        $log_file = LOG_PATH . ltrim($log_name, "/"); //保存在runtime/log/目录下
        $path = dirname($log_file);
        !is_dir($path) && @mkdir($path, 0755, true); //创建目录

        @file_put_contents($log_file, $content, FILE_APPEND);
    }

    /**
     * 获取分页html
     * @param int $total 总记录数
     * @param int $page_size 每页记录数
     * @return string
     */
    public static function getPageHtml($total, $page_size)
    {
        $request = request();
        $page = intval($request->get('page', 1));
        $page_num = $page_size > 0 ? ceil($total / $page_size) : 0;

        $page_html = Pager::instance(['total' => $total, 'limit' => $page_size])
               ->render($page, $page_num, $request->get());

        return $page_html;
    }

    /**
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public static function outJson($code = 0, $msg = '', $data = [])
    {
        if (!$data) {
            return [
                "code" => $code,
                "msg" => $msg
            ];
        } else {
            return [
                "code" => $code,
                "msg" => $msg,
                "data" => $data
            ];
        }
    }

    /**
     * 把数据集转换成Tree
     * @param array $list
     * @param string $pk 主键
     * @param string $pid 父级id
     * @param string $child
     * @param int $root 根节点id
     * @return array
     */
    public static function buildTree(&$list, $pk = 'id', $pid = 'pid', $child = 'child', $root = 0)
    {
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
     *
     * @param array $tree
     * @param array $result
     * @param int $deep
     * @param string $separator
     * @return array
     */
    public static function getTreeDropDownList($tree = [], &$result = [], $deep = 0, $separator = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
    {
        $deep++;
        foreach ($tree as $list) {
            $result[$list['id']] = str_repeat($separator, $deep - 1) . $list['name'];
            if (isset($list['child'])) {
                self::getTreeDropDownList($list['child'], $result, $deep);
            }
        }
        return $result;
    }

    /**
     * 获取微秒时间
     * @return float
     */
    public static function getMicroTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }
}