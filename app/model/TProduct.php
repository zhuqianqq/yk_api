<?php
/**
 * 商品表
 */

namespace app\model;

use think\facade\Db;

class TProduct extends BaseModel
{
    protected $table = "t_product";

    public static $IS_ONLINE_ARR = [
        "1" => "上架",
        "0" => "下架"
    ];


    /**
     * 分页查询列表
     * @param int $page 当前页号从1开始
     * @param int $page_size 每页记录数
     * @param array $where
     * @return array
     */
    public static function getList($page, $page_size, $where = [])
    {
        $where["is_del"] = 0; //已删除的不显示
        $query = Db::table("t_product")->field("prod_id,prod_name,price,stock,first_img,user_id,is_online")
            ->where($where);

        $total = $query->count(); //总记录条数

        $offset = ($page - 1) * $page_size;
        $list = $query->limit($offset, $page_size + 1)//多查一条
                ->select();

        $has_next = 0; //是否有下一页 0:无，1：有
        self::checkHasNextPage($list,$page_size,$has_next);

        return [$list, $total, $has_next];
    }
}
