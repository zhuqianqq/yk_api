<?php
/**
 * Model基类
 */
namespace app\model;

use think\Model;
use think\facade\Db;
use think\Collection;

abstract class BaseModel extends Model
{
    /**
     * 判断
     */
    const IS_YES = 1;
    const IS_NO = 0;

    public static $isLabels = [
        self::IS_NO => '否',
        self::IS_YES => '是'
    ];

    const IS_DEL_NO = 0; //否
    const IS_DEL_YES = 1;//是

    /**
     * @var string 错误信息
     */
    protected $error;

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error ?? '';
    }

    /**
     * 查询总记录数
     * @param string $sql sql语句
     * @param array $bind 绑定的参数
     * @return int
     */
    public static function queryTotal($sql,$bind = [])
    {
        $total = 0;
        $res = Db::query($sql,$bind);
        if($res){
            $total = intval(current($res[0]));
        }
        return $total;
    }

    /**
     * 记录数
     * @param mixed $where  查询条件
     * @return int
     */
    public static function count($where)
    {
        $model = new static();

        $query = $model->db();

        return $query->where($where)->count();
    }

    /**
     * 是否有下一页记录
     * @param Collection $list
     * @param int $page_size 每页记录条数
     * @param int $next 是否有下一页 0-无，1-有
     */
    public static function checkHasNextPage(&$list,$page_size,&$next)
    {
        $next = 0;
        if (count($list) > $page_size){
            $list = $list->slice(0,$page_size);
            $next = 1;
        }
    }
}
