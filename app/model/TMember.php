<?php
/**
 * 会员表
 */
namespace app\model;

use think\facade\Db;

class TMember extends BaseModel
{
    protected $table = "t_member";

    /**
     * @var array 是否锁定
     */
    public static $IS_LOCk_ARR = [
        "1" => "锁定",
        "0" => "正常"
    ];

    /**
     * @var array 是否已实名
     */
    public static $AUDIT_STATUS_ARR = [
        "1" => "是",
        "0" => "否"
    ];


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
