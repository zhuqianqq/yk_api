<?php
/**
 * 商品管理
 */

namespace app\controller;

use App\Models\Cfund\Project;
use app\model\TProduct;
use think\facade\Db;
use app\model\TProductProperty;
use app\model\TProductDetail;

class ProductController extends BaseController
{
    protected $middleware = [
        'access_check' => ['only' => ['up', 'down', 'del']],
    ];

    /**
     * 商品列表
     */
    public function prodList()
    {
        $page = $this->request->param("page", 1, "intval");
        $page_size = $this->request->param("page_size", 10, "intval");
        $user_id = $this->request->param("user_id", 0, "intval");

        if ($user_id <= 0) {
            return $this->outJson(100, "user_id无效");
        }

        $where["user_id"] = $user_id;
        list($list, $total, $has_next) = TProduct::getList($page, $page_size, $where);

        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];

        return $this->outJson(0, "success", $data);
    }

    /**
     * 上架商品数
     */
    public function upCount()
    {
        $user_id = $this->user_id;

        if ($user_id <= 0) {
            return $this->outJson(100, "user_id无效");
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

        if (empty($prod_name)) {
            return $this->outJson(100, "商品名称不能为空");
        }
        if ($price <= 0) {
            return $this->outJson(100, "价格不能为空");
        }
        if ($stock <= 0) {
            return $this->outJson(100, "库存不能为空");
        }
        if (empty($head_img)) {
            return $this->outJson(100, "头部图片不能为空");
        }
        if (empty($prop_list)) {
            return $this->outJson(100, "商品规则不能为空");
        }

        Db::startTrans();
        try {
            $head_img_arr = json_decode($head_img, true);
            $first_img = $head_img_arr ? $head_img_arr[0] : ''; //首图
            if ($prod_id > 0) {
                //编辑
                $prod_model = TProduct::where("prod_id", $prod_id)->find();
                if (empty($prod_model)) {
                    return $this->outJson(200, "商品不存在");
                }
                if ($prod_model->user_id != $this->user_id) {
                    return $this->outJson(200, "你无权编辑该商品");
                }
                //编辑后，要变成上架状态
                $prod_model->prod_name = $prod_name;
                $prod_model->price = $price;
                $prod_model->stock = $stock;
                $prod_model->weight = $weight;
                $prod_model->wechat = $wechat;
                $prod_model->first_img = $first_img; //首图
                $prod_model->update_time = date("Y-m-d H:i:s");
                $prod_model->save();

                TProductProperty::where('proj_id', $prod_id)->delete();
                //商品规则
                TProductProperty::addPropList($prop_list);
                //商品详情
                TProductDetail::update([
                    "head_img" => $head_img,
                    "detail" => $detail,
                ], ["prod_id" => $prod_id]);
            } else {
                //新增
                $prod_model = new TProduct();
                $prod_model->user_id = $this->user_id;
                $prod_model->prod_name = $prod_name;
                $prod_model->price = $price;
                $prod_model->stock = $stock;
                $prod_model->weight = $weight;
                $prod_model->wechat = $wechat;
                $prod_model->first_img = $first_img; //首图
                $prod_model->create_time = date("Y-m-d H:i:s");

                $prod_model->save();

                //商品规则
                TProductProperty::addPropList($prop_list);

                //详情
                $detail_model = new TProductDetail();
                $detail_model->prod_id = $prod_model->prod_id;
                $detail_model->head_img = $head_img;
                $detail_model->detail = $detail;
                $detail_model->save();
            }

            Db::commit();
            return $this->outJson(0, "success", ["prod_id" => $prod_model->prod_id]);
        } catch (\Exception $ex) {
            Db::rollback();
            return $this->outJson(500, "接口异常:" . $ex->getMessage());
        }
    }
}
