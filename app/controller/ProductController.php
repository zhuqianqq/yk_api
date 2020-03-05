<?php
/**
 * 商品管理
 */
namespace app\controller;

use App\Models\Cfund\Project;
use app\model\TProduct;

class ProductController extends BaseController
{
    protected $middleware = [
        'access_check' 	=> ['only' 	=> ['up','down','del'] ],
    ];

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
     * 上架商品数
     */
    public function upCount()
    {
        $user_id = $this->request->param("user_id",0,"intval");

        if($user_id <= 0){
            return $this->outJson(100, "user_id无效");
        }

        $data = [
            "total" => TProduct::count([
                'user_id'=>$user_id,
                'is_online'=>1,
                'is_del'=>0
            ])
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
        $prod_id = $this->request->param("prod_id",0,"intval");

        if($prod_id <= 0){
            return $this->outJson(100,"参数错误");
        }

        $data = TProduct::getDetail($prod_id);

        if($data){
            return $this->outJson(0,"success",$data);
        }else{
            return $this->outJson(200,"商品不存在");
        }
    }

    /**
     * 商品新增
     * @return array
     */
    public function add()
    {
        $prod_id = $this->request->param("prod_id",0,"intval");

        if($prod_id <= 0){
            return $this->outJson(100,"参数错误");
        }

        $data = TProduct::getDetail($prod_id);

        if($data){
            return $this->outJson(0,"success",$data);
        }else{
            return $this->outJson(200,"商品不存在");
        }
    }
}
