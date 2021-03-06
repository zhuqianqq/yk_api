<?php
/**
 * 商品管理
 */

namespace app\controller;

use app\model\shop\MallGoods;
use app\model\TMember;
use app\model\TProductRecommend;
use app\model\TRoom;
use App\Models\Cfund\Project;
use app\model\TProduct;
use app\util\Tools;
use think\facade\Db;
use app\model\TProductProperty;

class ProductController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['up', 'down', 'del', 'save']],
    ];

    /**
     * 用户商品列表
     */
    public function prodList()
    {
        $page = $this->request->param("page", 1, "intval");
        $page_size = $this->request->param("page_size", 10, "intval");
        $user_id = $this->request->param("user_id", 0, "intval");
        $scece = $this->request->param("scece", "", "trim"); //场景

        if ($user_id <= 0) {
            return $this->outJson(100, "user_id无效");
        }

        $where["user_id"] = $user_id;
        if ($scece == "live") { //直播间，用户商品只返回上架的
            $order = ["weight" => 'desc'];
            $where["is_online"] = 1;
        } else {
            $order = ["is_online" => "desc", "weight" => 'desc'];
        }

        list($list, $total, $has_next) = TProduct::getList($page, $page_size, $where, $order);

        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];

        return $this->outJson(0, "success", $data);
    }

    /**
     * 查询主播的上架商品数
     */
    public function upCount()
    {
        $user_id = $this->request->param("user_id", 0, "intval"); //主播id
        $room_id = $this->request->param('room_id', '', "trim");  //房间id

        if ($user_id <= 0) {
            return $this->outJson(100, "user_id无效");
        }
        //判断该直播是否展示商品
        if ($room_id) {
            $show_product = TRoom::where("room_id", $room_id)->value("show_product");
            if (!$show_product) {
                return $this->outJson(0, "success", [
                    "total" => 0,
                ]);
            }
        }

        $data = [
            "total" => TProduct::count([
                'user_id' => $user_id,
                'is_online' => 1,
                'is_del' => 0
            ])
        ];


        return $this->outJson(0, "success", $data);
    }

    /**
     * 上架
     */
    public function up()
    {
        $prod_id = $this->request->post("prod_id", 0, "intval");
        $user_id = $this->request->post("user_id", 0, "intval");

        if ($prod_id <= 0 || $user_id <= 0) {
            return $this->outJson(100, "参数错误");
        }

        $ret = TProduct::where([
            "prod_id" => $prod_id,
            "user_id" => $user_id,
        ])->update([
            "is_online" => 1,
            "update_time" => date("Y-m-d H:i:s")
        ]);

        if ($ret) {
            return $this->outJson(0, "上架成功");
        }
        return $this->outJson(200, "操作失败");
    }

    /**
     * 下架
     */
    public function down()
    {
        $prod_id = $this->request->post("prod_id", 0, "intval");
        $user_id = $this->request->post("user_id", 0, "intval");

        if ($prod_id <= 0 || $user_id <= 0) {
            return $this->outJson(100, "参数错误");
        }

        $ret = TProduct::where([
            "prod_id" => $prod_id,
            "user_id" => $user_id,
        ])->update([
            "is_online" => 0,
            "update_time" => date("Y-m-d H:i:s")
        ]);

        if ($ret) {
            return $this->outJson(0, "下架成功");
        }
        return $this->outJson(200, "操作失败");
    }

    /**
     * 删除
     */
    public function del()
    {
        $prod_id = $this->request->post("prod_id", 0, "intval");
        $user_id = $this->request->post("user_id", 0, "intval");

        if ($prod_id <= 0 || $user_id <= 0) {
            return $this->outJson(100, "参数错误");
        }

        $ret = TProduct::where([
            "prod_id" => $prod_id,
            "user_id" => $user_id,
        ])->update([
            "is_del" => 1,
            "update_time" => date("Y-m-d H:i:s")
        ]);

        if ($ret) {
            return $this->outJson(0, "删除成功");
        }
        return $this->outJson(200, "操作失败");
    }

    /**
     * 商品详情
     */
    public function detail()
    {
        $prod_id = $this->request->param("prod_id", 0, "intval");

        if ($prod_id <= 0) {
            return $this->outJson(100, "参数错误");
        }

        $data = TProduct::getDetail($prod_id);

        if ($data) {
            return $this->outJson(0, "success", $data);
        } else {
            return $this->outJson(200, "商品不存在");
        }
    }

    /**
     * 商品新增
     * @return array
     */
    public function save()
    {
        $prod_id = $this->request->param("prod_id", 0, "intval");
        $prod_name = $this->request->param("prod_name", '', "trim");
        $price = $this->request->param("price", 0, "floatval");
        $stock = $this->request->param("stock", 0, "intval");
        $weight = $this->request->param("weight", 0, "intval");
        $wechat = $this->request->param("wechat", '', "trim");
        $head_img = $this->request->param("head_img", '', "trim"); //头部图片地址,json数组格式
        $prop_list = $this->request->param("prop_list", '', "trim"); //商品规格属性
        $detail = $this->request->param("detail", '', "trim"); //图文详情，json格式，前端自定义格式
        $detail = $detail == "null" ? '' : $detail;

        $input = $this->request->getInput();

        if (empty($prod_name)) {
            Tools::addLog("prod_save", "商品名称不能为空", $input);
            return $this->outJson(100, "商品名称不能为空");
        }
        if ($price <= 0) {
            Tools::addLog("prod_save", "价格不能小于0", $input);
            return $this->outJson(100, "价格不能小于0");
        }
//        if ($stock <= 0) {
//            Tools::addLog("prod_save","库存不能为空",$input);
//            return $this->outJson(100, "库存不能为空");
//        }
        if (empty($head_img)) {
            Tools::addLog("prod_save", "头部图片不能为空", $input);
            return $this->outJson(100, "头部图片不能为空");
        }
        if (empty($prop_list)) {
            Tools::addLog("prod_save", "商品规则不能为空", $input);
            return $this->outJson(100, "商品规则不能为空");
        }

        Db::startTrans();
        try {
            $head_img_arr = json_decode($head_img, true);
            $first_img = $head_img_arr ? $head_img_arr[0] : ''; //首图

            $tb_prod = Db::table("t_product");
            $tb_detail = Db::table("t_product_detail");
            if ($prod_id > 0) {
                //编辑
                $prod_model = $tb_prod->where("prod_id", $prod_id)->find();
                if (empty($prod_model)) {
                    Tools::addLog("prod_save", "商品不存在", $input);
                    return $this->outJson(200, "商品不存在");
                }
                if ($prod_model["user_id"] != $this->user_id) {
                    Tools::addLog("prod_save", "你无权编辑该商品", $input);
                    return $this->outJson(200, "你无权编辑该商品");
                }
                $tb_prod->where("prod_id", $prod_id)->update([
                    "prod_name" => $prod_name,
                    "price" => $price,
                    "stock" => $stock,
                    "weight" => $weight,
                    "wechat" => $wechat,
                    "first_img" => $first_img,  //首图
                    "update_time" => date("Y-m-d H:i:s"),
                ]);

                //商品规则
                TProductProperty::addPropList($prod_id, $prop_list);
                //商品详情
                $tb_detail->where("prod_id", $prod_id)->update([
                    "head_img" => $head_img,
                    "detail" => $detail,
                ]);
            } else {
                //新增
                $prod_id = $tb_prod->insertGetId([
                    "user_id" => $this->user_id,
                    "prod_name" => $prod_name,
                    "price" => $price,
                    "stock" => $stock,
                    "weight" => $weight,
                    "wechat" => $wechat,
                    "first_img" => $first_img,  //首图
                    "create_time" => date("Y-m-d H:i:s"),
                ]);
                //商品规则
                TProductProperty::addPropList($prod_id, $prop_list);
                //详情
                $tb_detail->insertGetId([
                    "prod_id" => $prod_id,
                    "head_img" => $head_img,
                    "detail" => $detail,
                ]);
            }
            Db::commit();
            Tools::addLog("prod_save", "success,projd_id:{$prod_id}", $input);

            return $this->outJson(0, "success", ["prod_id" => $prod_id]);
        } catch (\Exception $ex) {
            Db::rollback();
            Tools::addLog("prod_save", "save_error:" . $ex->getMessage() . PHP_EOL . $ex->getTraceAsString(), $input);
            return $this->outJson(500, "接口异常:" . $ex->getMessage());
        }
    }

    /*
     * 增加推荐商品
     */
    public function addRecommend()
    {
        $prod_id = $this->request->post("prod_id", 0, "intval");
        $user_id = $this->request->post("user_id", 0, "intval");
        if ($prod_id <= 0 || $user_id <= 0) {
            return $this->outJson(100, "参数错误！");
        }

        $addRes = TProductRecommend::addRecommendProduct($user_id, $prod_id);
        if ($addRes === false) {
            return $this->outJson(100, "最多只能推荐两个！");
        }
        $data = TProductRecommend::getRecommendList($user_id);
        $products = MallGoods::getRecommandGoods($data);
        return $this->outJson(0, "推荐商品成功！", $products);
    }

    /**
     * 移除推荐商品
     */
    public function removeRecommend()
    {
        $prod_id = $this->request->post("prod_id", 0, "intval");
        $user_id = $this->request->post("user_id", 0, "intval");
        if ($prod_id <= 0 || $user_id <= 0) {
            return $this->outJson(100, "参数错误！");
        }

        $res = TProductRecommend::removeRecommend($user_id, $prod_id);

        return $res ? $this->outJson(0, "移除推荐商品！") : $this->outJson(100, "移除商品失败");;
    }

    /*
     * 取推荐商品列表
     */
    public function getRecommendItem()
    {
        $user_id = $this->request->post("user_id", 0, "intval");
        $data = TProductRecommend::getRecommendList($user_id);
        //Tools::addLog("recommendGood","推荐商品编号",json_encode($data));
        $products = MallGoods::getRecommandGoods($data);
        if(count($products)==0){
            return $this->outJsonWithNullData(0, "当前推荐商品！");
        }
        //Tools::addLog("recommendGood","推荐商品",json_encode($products));
        return $this->outJson(0, "当前推荐商品！", $products);
    }
}
