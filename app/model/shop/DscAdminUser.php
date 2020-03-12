<?php
/**
 * 商城平台用户表及卖家(seller)用户表
 */
namespace app\model\shop;

use app\model\shop\ShopBaseModel;
use think\facade\Db;

class DscAdminUser extends ShopBaseModel
{
    protected $table = "dsc_admin_user";
}
