<?php
/**
 * 商品管理
 */
namespace app\controller;

use App\Models\Cfund\Project;
use app\model\TProduct;

class ProductController extends BaseController
{
    /**
     * 商品列表
     */
    public function prodList()
    {
        $page = $this->request->param("page",1,"intval");
        $page_size = $this->request->param("page_size",10,"intval");
        $user_id = $this->request->param("user_id",0,"intval");

        if($user_id <= 0){
            return $this->outJson(100,"user_id无效");
        }

        $where["user_id"] = $user_id;
        list($list,$total,$has_next) = TProduct::getList($page,$page_size,$where);

        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];

        return $this->outJson(0,"success",$data);
    }

    /**
     * 上架
     */
    public function up()
    {
        $prod_id = $this->request->post("prod_id",0,"intval");
        $user_id = $this->request->post("user_id",0,"intval");

        if($prod_id <= 0 || $user_id <= 0){
            return $this->outJson(100,"参数错误");
        }

        $ret = TProduct::where([
            "prod_id" => $prod_id,
            "user_id" => $user_id,
        ])->update([
            "is_online" => 1,
            "update_time" => date("Y-m-d H:i:s")
        ]);

        if($ret){
            return $this->outJson(0,"上架成功");
        }
        return $this->outJson(200,"操作失败");
    }

    /**
     * 下架
     */
    public function down()
    {
        $prod_id = $this->request->post("prod_id",0,"intval");
        $user_id = $this->request->post("user_id",0,"intval");

        if($prod_id <= 0 || $user_id <= 0){
            return $this->outJson(100,"参数错误");
        }

        $ret = TProduct::where([
            "prod_id" => $prod_id,
            "user_id" => $user_id,
        ])->update([
            "is_online" => 0,
            "update_time" => date("Y-m-d H:i:s")
        ]);

        if($ret){
            return $this->outJson(0,"下架成功");
        }
        return $this->outJson(200,"操作失败");
    }

    /**
     * 删除
     */
    public function del()
    {
        $prod_id = $this->request->post("prod_id",0,"intval");
        $user_id = $this->request->post("user_id",0,"intval");

        if($prod_id <= 0 || $user_id <= 0){
            return $this->outJson(100,"参数错误");
        }

        $ret = TProduct::where([
            "prod_id" => $prod_id,
            "user_id" => $user_id,
        ])->update([
            "is_del" => 1,
            "update_time" => date("Y-m-d H:i:s")
        ]);

        if($ret){
            return $this->outJson(0,"删除成功");
        }
        return $this->outJson(200,"操作失败");
    }

    /**
     * 商品详情
     */
    public function detail()
    {

    }
}
