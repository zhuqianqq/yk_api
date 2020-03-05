<?php
/**
 * 商品表
 */

namespace app\model;

use app\util\Tools;
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
                         p.user_id,p.is_online,p.is_del,pd.head_img,pd.detail")
                ->where($where)
                ->find();

        if($data){
            $data["head_img"] = $data["head_img"] ? explode(";",$data['head_img']) : [];
            $data["detail"] = $data["detail"] ? json_decode($data["detail"],true) : null;
            $data["prop_list"] = TProductProperty::getPropertyList($prod_id);
        }
        return $data;
    }

    public static function addProduct()
    {

    }

    public static function editProduct($prod_id)
    {
        $prod_model = TProduct::where("prod_id",$prod_id)->find();
        if(empty($prod_model)){
            return Tools::outJson(200,"商品不存在");
        }
        if($prod_model->user_id != $this->user_id){
            return Tools::outJson(200,"你无权编辑该商品");
        }
        //编辑后，要变成上架状态
        $prod_model->prod_name = $prod_name;
        $prod_model->price = $price;
        $prod_model->stock = $stock;
        $prod_model->weight = $weight;
        $prod_model->wechat = $wechat;
        $prod_model->first_img = $first_img; //首图
        $prod_model->update_time = date("Y-m-d H:i:s");
    }
}
