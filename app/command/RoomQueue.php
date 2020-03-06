<?php
/**
 * 消费队列
 */
namespace app\command;

use think\facade\Cache;

class RoomQueue extends BaseCommand
{
    /**
     * @var string 指令名称
     */
    protected $scriptName = "room_queue";

    /**
     * 执行入口
     */
    protected function _execute()
    {
        $redis = Cache::getHandler();
        while(true){
            if ($this->checkScriptStop()){
                $this->log("script stop");
                break;
            }
            $data = $redis->brPop(QxbBusinessInfo::$QUEUE_KEY,10);
            if(empty($data)){
                $this->log("empty");
                sleep(5);
                continue;
            }
            $this->log($data);
            $busi_sn = $data["busi_sn"] ?? '';
            if(empty($busi_sn)){
                continue;
            }
            $cache_key = "qxb::{$busi_sn}";
            if(Cache::get($cache_key) == 1){
                $this->log("$cache_key is exist");
                continue;
            }
            $oper_user = $data['oper_user'] ?? '';
            $qxbBusinessInfo = new QxbBusinessInfo();
            $result = QiXinBao::getDetailByName($busi_sn);
            $this->log($result);

            if($result['status'] == 200){
                $param = $result['data'];
                $param['oper_user'] = $oper_user;
                $ret = $qxbBusinessInfo->createOrUpdateData($param);
                if($ret){
                    Cache::set($cache_key,1,6 * 3600);  //6小时去重
                }
            }else{
                $param['message'] = $result['message'];
                $param['oper_user'] = $oper_user;
                $qxbBusinessInfo->createErrorData($param, $busi_sn);
            }
        }
        $this->log("done");
    }
}