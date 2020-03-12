<?php


namespace app\model;


class TProductRecommend extends BaseModel
{

    protected $table = "t_product_recommend";

    public function addRecommendProduct($user_id,$prod_id){
        $recommendItem = new TProductRecommend();
        $recommendItem->user_id = $user_id;
        $recommendItem->product_id = $prod_id;
        $recommendItem->create_time = date("Y-m-d H:i:s");
        $recommendItem->save();
    }

    public function getRecommendList($user_id){
        $data = TProductRecommend::where("user_id",$user_id)->order("create_time","desc")->limit(2)->select();
        return $data;
    }
}