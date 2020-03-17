<?php

namespace app\command;

use app\model\shop\MallShop;
use think\console\input\Argument;
use think\facade\Config;
use app\model\TMember;
use app\model\shop\MallUser;

class ToolCommand extends BaseCommand
{
    /**
     * @var string 指令名称
     */
    protected $scriptName = "tool";

    protected function configure()
    {
        parent::configure();
        $this->addArgument("user_id",Argument::OPTIONAL); //添加一个参数
    }

    /**
     * 执行入口(处理业务逻辑)
     */
    protected function _execute()
    {
        $user_id = intval($this->input->getArgument("user_id"));

        if($user_id <= 0){
            $this->output->error("user_id 不能小于0");
            return 0;
        }

        $user_info = TMember::getById($user_id);
        if(empty($user_info)){
            $this->output->error("user_id 不存在");
            return 0;
        }

        $this->output->write("开始注册商城用户：>>>>>>",true);
        $mall_user_id = MallUser::register($user_info); //注册商城用户
        $this->output->write("返回商城用户id:".$mall_user_id,true);

        if($mall_user_id > 0 && $user_info['is_broadcaster'] == 1){
            $this->output->write("开始创建主播店铺：>>>>",true);
            $shop_id = MallShop::openShop($mall_user_id,$user_info);

            $this->output->write("返回shop_id:".$shop_id,true);
        }else{
            $this->output->write("非主播用户End",true);
        }
    }
}