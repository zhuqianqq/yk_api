<?php
namespace app\model;

use think\facade\Db;

class TRoom extends BaseModel
{
    protected $table = "t_room";

    public static function getList($page,$page_size){
        $page = $page ? $page : 1;
        $query = Db::table("t_room r")
            ->leftJoin("t_member m", "r.user_id=m.user_id ")
            ->field("r.*,m.nick_name,m.avatar,m.display_code");
        $list=$query->page($page, $page_size)->select();
        $total = $query->count();
        $has_next=0;
        self::checkHasNextPage($list,$page_size,$has_next);
        $data = [
            "list" => $list,
            "current_page" => $page,
            "total" => $total,
            "has_next" => $has_next, //是否有下一页
        ];
        return $data;
    }


}
