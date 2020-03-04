<?php
/**
 * TDict 字典表
 */

namespace app\model;

class TDict extends BaseModel
{
    protected $table = "TDict";

    /**
     * 根据类型获取相关字典信息
     * @param string $type 类型
     * @return array
     */
    public static function getInfoByType($type)
    {
        $list = self::where("Type", $type)->field("ItemName,ItemValue")->select();
        $result = [];
        if (!empty($list)) {
            foreach ($list as &$row) {
                $result[$row['ItemName']] = $row['ItemValue'];
            }
        }

        return $result;
    }

    /**
     * @param $type
     * @param $item_name
     * @return array|\think\Model|null
     */
    public static function getItemRow($type, $item_name)
    {
        $obj = self::where("Type", $type)->where("ItemName",$item_name)->field("ItemName,ItemValue,Remark")->find();
        return $obj;
    }

    /**
     * 获取字典键值
     * @param string $type 对应字典表中的字段
     * @param string $item_name 对应字典表中的字段
     * @param string $defval 查询失败情况下默认返回的值
     * @return string 字典表中的键值
     */
    public static function getItemValue($type, $item_name, $defval = '')
    {
        $obj = self::getItemRow($type,$item_name);

        return !empty($obj) ? $obj["ItemValue"] : $defval;
    }
}
