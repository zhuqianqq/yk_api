<?php
/**
 * 商品管理
 */

namespace app\controller;

use app\util\Tools;
use think\facade\Db;
use app\model\shop\DscOrder;
use app\model\TUserMap;

//本地测试 http://www.yk-api.com/index.php/order/salerOrderList?user_id=1324&type=1
class OrderController extends BaseController
{
    //protected $middleware = ['access_check'];

    //卖家订单查询列表接口
    public function salerOrderList()
    {
        $page = $this->request->param("page", 1, "intval");
        $page_size = $this->request->param("page_size", 10, "intval");
        $user_id = $this->request->param("user_id", 0, "intval");
        $pay_type = $this->request->param("type", 0, "intval");

        if ($user_id <= 0) {
            return $this->outJson(100, "user_id无效");
        }

        //映客直播玩家id转换商城id
        $user_id = TUserMap::getShopUserId($user_id,'shop_user_id');
        $user_id = 127;//测试写死用
        $where["user_id"] = $user_id;
        
        $order = ["pay_status" => "asc","shipping_status" => 'asc'];

        list($list, $total, $has_next) = DscOrder::getList($page, $page_size, $where, $order, $pay_type);

        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];
        return $this->outJson(0, "success", $data);
    }

    //我的订单查询列表接口
    public function MyOrderList()
    {
        $page = $this->request->param("page", 1, "intval");
        $page_size = $this->request->param("page_size", 10, "intval");
        $user_id = $this->request->param("user_id", 0, "intval");
        //0 全部订单 1 待付款订单 2 待收货订单  3 已完成订单
        $pay_type = $this->request->param("type", 0, "intval");

        if ($user_id <= 0) {
            return $this->outJson(100, "user_id无效");
        }

        //映客直播玩家id转换商城id
        $user_id = TUserMap::getShopUserId($user_id,'shop_user_id');
        $user_id = 127;//测试写死用
        $where["user_id"] = $user_id;
      
        $order = ["pay_status" => "asc","shipping_status" => 'asc'];

        list($list, $total, $has_next) = DscOrder::getMyList($page, $page_size, $where, $order, $pay_type);

        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];

        return $this->outJson(0, "success", $data);
    }
}
