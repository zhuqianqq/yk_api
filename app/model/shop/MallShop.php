<?php
/**
 * 商城卖家店铺表
 */
namespace app\model\shop;

use app\model\shop\MallBaseModel;
use app\model\TMember;
use app\util\Tools;
use think\facade\Db;
use app\model\TUserMap;

class MallShop extends MallBaseModel
{
    protected $table = "mall_shops";

    /**
     * @var string 主键
     */
    protected $pk = 'shopId';

    /**
     * 申请状态,未提交，填写中
     */
    const APPLY_STATUS_DEAULT = 0;

    /**
     * 已提交
     */
    const APPLY_STATUS_COMMITED = 1;

    /**
     * 审核通过
     */
    const APPLY_STATUS_PASS = 2;

    /**
     * @param int $shop_id
     * @param string $field
     */
    public static function getInfoById($shop_id,$field = "*")
    {
        return self::where("shopId",$shop_id)->where("dataFlag",1)->field($field)->find();
    }

    /**
     * @param string $shop_sn
     * @param string $field
     */
    public static function getInfoBySn($shop_sn,$field = "*")
    {
        return self::where("shopSn",$shop_sn)->where("dataFlag",1)->field($field)->find();
    }

    /**
     * 为卖家开通店铺
     * @param int $user_id 商城用户id
     * @param array $user_info
     * @return int
     */
    public static function openShop($user_id,$user_info = [])
    {
        $shop = self::where('userId',$user_id)->find();
        if(!empty($shop)){
            return $shop['shopId'];
        }

        $db_mall = Db::connect("mall");
        $db_mall->startTrans();
        $shop_id = 0;
        try{
            $shop_name = $user_info["nick_name"] ? $user_info["nick_name"] : $user_info["display_code"];
            $shopSn = self::getShopSn("S");
            $ins_data = [
                'userId' => $user_id,
                'shopSn' => $shopSn,
                'shopName' => $shop_name.'之家',
                'telephone' => $user_info["phone"] ?? '',
                'areaIdPath' => '2_52_500_',
                'areaId' => '500',
                'isSelf' => '1', //是否自营
                'areaIdPath' => '2_52_500_',
                'applyStep' => 3, //申请步骤
                'applyStatus' => self::APPLY_STATUS_PASS,  //审核通过
                'applyTime' => date("Y-m-d H:i:s"), //申请时间
                "createTime" => date("Y-m-d H:i:s"),
            ];
            $shop_id = self::insertGetId($ins_data);

            if($shop_id){
                //更改用户身份为卖家
                $db_mall->name('users')->where('userId', $user_id)->update(['userType' => MallUser::USER_TYPE_SELLER]);

                //扩展字段表
                $exData = [
                    'shopId' => $shop_id,
                    'businessStartDate' => date("Y-m-d"), //营业开始日期
                    'businessEndDate' => date("Y-m-d",strtotime("+5 years")), //营业结束日期
                ];
                $db_mall->name('shop_extras')->insert($exData);

                //经营范围
                $db_mall->name('cat_shops')->insert(['shopId' => $shop_id, 'catId' => 1]);

                //店铺配置表
                $sc = [];
                $sc['shopId'] = $shop_id;
                $db_mall->name('shop_configs')->insert($sc);

                //店铺用户表
                $su = [];
                $su["shopId"] = $shop_id;
                $su["userId"] = $user_id;
                $su["roleId"] = 0;
                $db_mall->name('shop_users')->insert($su);

                //建立店铺评分记录
                $ss = [];
                $ss['shopId'] = $shop_id;
                $db_mall->name('shop_scores')->insert($ss);
            }

            $db_mall->commit();
        }catch (\Exception $ex){
            Tools::addLog("open_shop","error user_id:{$user_id},ex:".$ex->getMessage().PHP_EOL.$ex->getTraceAsString(),$user_info);
            $db_mall->rollback();
        }
        Tools::addLog("open_shop","succes user_id:{$user_id},shop_id:{$shop_id}",$user_info);
        return $shop_id;
    }


    /**
     * 生成店铺编号
     * @param $key 编号前缀,要控制不要超过int总长度，最好是一两个字母
     */
    public static function getShopSn($key = '')
    {
        $rs = self::max(Db::raw("REPLACE(shopSn,'S','') + ''")); //取db最大编号

        if ($rs == '') {
            return $key . '000000001';
        } else {
            for ($i = 0; $i < 1000; $i++) {
                $num = (int)str_replace($key, '', $rs);
                $shopSn = $key . sprintf("%09d", ($num + 1)); //新的sn号
                $ischeck = self::checkShopSn($shopSn);
                if (!$ischeck){
                    return $shopSn;
                }
            }
            return '';//一直都检测到那就不要强行添加了
        }
    }

    /**
     * 检测店铺编号是否存在
     */
    public static function checkShopSn($shopSn, $shopId = 0)
    {
        $query = self::where(['shopSn' => $shopSn, 'dataFlag' => 1]);
        if ($shopId > 0) {
            $query->where('shopId', '<>', $shopId);
        }
        $num = $query->count();

        if ($num == 0) {
            return false;
        }
        return true;
    }
}
