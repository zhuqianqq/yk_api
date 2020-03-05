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


    /**
     * 商品详情
     * @param $prod_id
     * @return array|null
     */
    public static function getDetail($prod_id)
    {
        $where = ["p.prod_id" => $prod_id];

        $data = Db::table("t_product p")
                ->leftJoin("t_product_detail pd","p.prod_id = pd.prod_id")
                ->field("p.prod_id,p.prod_name,p.first_img,p.price,p.stock,p.weight,p.wechat,
                         p.user_id,p.is_online,p.is_del,pd.head_img,pd.detail_desc,pd.detail_imgs")
                ->where($where)
                ->find();

        if($data){
            $data["head_img"] = $data["head_img"] ? explode(";",$data['head_img']) : [];
            $data["detail_desc"] = $data["detail_desc"] ?? '';
            $data["detail_imgs"] = $data["detail_imgs"] ? explode(";",$data['detail_imgs']) : [];
        }
        return $data;
    }
}
