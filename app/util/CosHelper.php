<?php
/**
 * 腾讯cos上传
 */
namespace app\util;

use think\facade\Config;
use app\util\Tools;

class CosHelper
{
    protected static $logName = "upload";

    /**
     * 上传文件
     * @param string $file_path 本地文件物理地址
     * @param string $file_ext 文件扩展名
     * @return array
     */
    public static function upload($file_path,$file_ext = '')
    {
        $cos_conf = Config::get("cos");
        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $cos_conf["COSKEY_BUCKET_REGION"],
                'credentials' => [
                    'secretId' => $cos_conf['COS_SECRETID'],
                    'secretKey' => $cos_conf['COSKEY_SECRECTKEY']
                ]));

        $bucket = $cos_conf["COSKEY_BUCKET"];
        $key = self::generateKey($file_path, $file_ext);
        try {
            $body = fopen($file_path, 'rb');
            $result = $cosClient->upload($bucket,$key,$body);

            Tools::addLog(self::$logName, "{$file_path},key:{$key},res:" . json_encode($result, JSON_UNESCAPED_UNICODE));

            if ($result && !empty($result["Location"])) {
                if(stripos($result["Location"],"http") === false){
                    $result["Location"] = "https://".$result["Location"];
                }
                return Tools::outJson(0, "上传成功", [
                    "url" => $result["Location"],
                    "key" => $key,
                ]);
            }
            return Tools::outJson(-1, "上传失败");
        } catch (\Exception $ex) {
            Tools::addLog(self::$logName, "{$file_path},upload_fail,line:" . $ex->getLine() . ",message:" . $ex->getMessage());
            return Tools::outJson(500, "上传失败:" . $ex->getMessage());
        }
    }

    /**
     * 用文件名和扩展名生成一个key
     * @param string $fileName
     * @param string $file_ext
     * @return string
     */
    private static function generateKey($fileName = '', $file_ext = '')
    {
        $fileExt = $file_ext ? $file_ext : Tools::getExtension($fileName);

        return Tools::getGuider("ygzb") .".".$fileExt;
    }
}