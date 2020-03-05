<?php
/**
 * 商品规格表
 */
namespace app\model;

use think\facade\Db;

class TProductProperty extends BaseModel
{
    protected $table = "t_product_property";

    /**
     * 获取商品规格属性列表
     * @param int $prod_id
     * @return array
     */
    public static function getPropertyList($prod_id)
    {
        $data = self::where("prod_id",$prod_id)->field("prop_name,prop_value")->select();

        if($data){
            $data = $data->toArray();
            foreach($data as &$row){
                $row["prop_value"] = $row["prop_value"] ? json_decode($row["prop_value"],true) : [];
            }
        }
        return $data;
    }
}
