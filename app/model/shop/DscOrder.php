<?php
/**
 * 用户表
 */
namespace app\model\shop;

use app\model\shop\MallBaseModel;
use think\facade\Db;

class DscOrder extends MallBaseModel
{
    protected $table = "dsc_order_info";

    /**
    * 分页查询列表
    * @param int $page 当前页号从1开始
    * @param int $page_size 每页记录数
    * @param array $where
    * @param array $order
    * @return array
    */
    public static function getList($page, $page_size, $where = [], $order, $pay_type)
    {
        //0 全部订单 1 待付款订单 2 待发货订单 3 已发货订单 4 已完成订单
        if ($pay_type === 1) {
            $where['order_status'] = 0;
            $where['pay_status'] = 0;
        } elseif ($pay_type === 2) {
            $where['order_status'] = 0;
            $where['pay_status'] = 2;
            $where['shipping_status'] = 0;
        } elseif ($pay_type === 3) {
            $where['order_status'] = 0;
            $where['pay_status'] = 2;
            $where['shipping_status'] = 1;
        } elseif ($pay_type === 4) {
            $where['order_status'] = 1;
        }

        $query = Db::connect('shop')->table("dsc_order_info")
        ->field("order_id,order_sn,user_id,order_status,shipping_status,pay_status")
        ->where($where);

        $total = $query->count(); //总记录条数

        $list = [];
        $has_next = 0; //是否有下一页 0:无，1：有
        if ($total > 0) {
            $offset = ($page - 1) * $page_size;
            $list = $query->limit($offset, $page_size)//多查一条
                    ->order($order)   //未付款 未发货优先排序
                    ->select()->toArray();

            self::getGoodsInfo($list); //获取订单对应的商品信息

            self::checkHasNextPage($list, $page_size, $has_next);
        }

        return [$list, $total, $has_next];
    }


    public static function getMyList($page, $page_size, $where = [], $order, $pay_type)
    {
        //0 全部订单 1 待付款订单 2 待收货订单  3 已完成订单
        if ($pay_type === 1) {
            $where['order_status'] = 0;
            $where['pay_status'] = 0;
        } elseif ($pay_type === 2) {
            $where['order_status'] = 0;
            $where['pay_status'] = 1;
            $where['shipping_status'] = 0;
        } elseif ($pay_type === 3) {
            $where['order_status'] = 1;
        } 

        $query = Db::connect('shop')->table("dsc_order_info")
        ->field("order_id,order_sn,user_id,order_status,shipping_status,pay_status")
        ->where($where);

        $total = $query->count(); //总记录条数

        $list = [];
        $has_next = 0; //是否有下一页 0:无，1：有
        if ($total > 0) {
            $offset = ($page - 1) * $page_size;
            $list = $query->limit($offset, $page_size)//多查一条
                    ->order($order)   //未付款 未发货优先排序
                    ->select()->toArray();

            self::getGoodsInfo($list); //获取订单对应的商品信息

            self::checkHasNextPage($list, $page_size, $has_next);
        }

        return [$list, $total, $has_next];
    }

    /**
     * 获取订单对应的商品信息
     * @param array 订单数组
     * @return array 在订单数组中追加了商品信息 引用传递
     */
    public static function getGoodsInfo(&$list){

        foreach ($list as $k => $v) {
            $good_info = Db::connect('shop')->table("dsc_order_goods")->alias('a')
            ->join(['dsc_goods'=>'b'],'a.goods_id=b.goods_id')
            ->field("a.goods_id,a.goods_name,a.goods_number,a.goods_price,a.goods_attr,b.goods_thumb")
            ->where(['a.order_id'=>$v['order_id']])
            ->select()->toArray();
            $list[$k]['goods_info'] = $good_info ?? [];
        }
    }


}
