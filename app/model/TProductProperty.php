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
     * @param int $prod_id
     * @param string $prop_list
     * @return integer
     */
    public static function addPropList($prod_id,$prop_list)
    {
        $tbl = Db::table("t_product_property");
        $tbl->where('prod_id', $prod_id)->delete();
        $prop_list = json_decode($prop_list,true);
        $data = [];
        foreach($prop_list as $prop){
            $data[] = [
                "prod_id" => $prod_id,
                "prop_name" => $prop["prop_name"],
                "prop_value" => json_encode($prop["prop_value"],JSON_UNESCAPED_UNICODE),
            ];
        }
        return $tbl->insertAll($data);
    }
}
