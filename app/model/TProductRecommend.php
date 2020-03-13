<?php


namespace app\model;


use function Sodium\add;

class TProductRecommend extends BaseModel
{

    protected $table = "t_product_recommend";

    public static function addRecommendProduct($user_id,$prod_id)
    {
        $data = TProductRecommend::where("user_id", $user_id)->order("create_time", "desc")->select()->toArray();
        $ids = array();
        $needAdd=false;
        foreach ($data as $k => $v) {
            if ($v['user_id'] == $user_id && $v['product_id'] == $prod_id && $k == 0) {

            } else {
                if ($k < 1) {

                } else {
                    $needAdd=true;
                    array_push($ids, $v['id']);
                }
            }
        }
        if($needAdd==true) {
            $recommendItem = new TProductRecommend();
            $recommendItem->user_id = $user_id;
            $recommendItem->product_id = $prod_id;
            $recommendItem->create_time = date("Y-m-d H:i:s");
            $recommendItem->save();
        }
        TProductRecommend::destroy($ids);
        return $data;
    }

    public static function getRecommendList($user_id){
        $data = TProductRecommend::where("user_id",$user_id)->field("product_id")->order("create_time","desc")->limit(2)->select();
        return $data;
    }
}