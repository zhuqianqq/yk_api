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

    /**
     * 新增商品规格
     * @param int $proj_id
     * @param string $prop_list
     * @return bool
     */
    public static function addPropList($proj_id,$prop_list)
    {
        $prop_list = json_decode($prop_list,true);
        foreach($prop_list as $prop){
            $prop_model = new TProductProperty();
            $prop_model->prod_id = $proj_id;
            $prop_model->prop_name = $prop["prop_name"];
            $prop_model->prop_value = $prop["prop_value"];
            $prop_model->save();
        }
        return true;
    }
}
