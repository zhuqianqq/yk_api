<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TencentCloud\Fmu\V20191213;
use TencentCloud\Common\AbstractClient;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Credential;
use TencentCloud\Fmu\V20191213\Models as Models;

/**
* @method Models\BeautifyPicResponse BeautifyPic(Models\BeautifyPicRequest $req) 输入人脸图片，输出美颜后的人脸图片。
* @method Models\CreateModelResponse CreateModel(Models\CreateModelRequest $req) 上传 LUT 格式文件注册唇色ID。最多允许上传1万张素材。
* @method Models\DeleteModelResponse DeleteModel(Models\DeleteModelRequest $req) 删除已注册的唇色素材。
* @method Models\GetModelListResponse GetModelList(Models\GetModelListRequest $req) 查询已注册的唇色素材。
* @method Models\TryLipstickPicResponse TryLipstickPic(Models\TryLipstickPicRequest $req) 对图片中的人脸嘴唇进行着色，最多支持同时对一张图中的3张人脸进行试唇色。

您可以通过事先注册在腾讯云的唇色素材（LUT文件）改变图片中的人脸唇色，也可以输入RGBA模型数值。

为了更好的效果，建议您使用事先注册在腾讯云的唇色素材（LUT文件）。

>     
- 公共参数中的签名方式请使用V3版本，即配置SignatureMethod参数为TC3-HMAC-SHA256。
 */

class FmuClient extends AbstractClient
{
    protected $endpoint = "fmu.tencentcloudapi.com";

    protected $version = "2019-12-13";

    function __construct($credential, $region, $profile=null)
    {
        parent::__construct($this->endpoint, $this->version, $credential, $region, $profile);
    }

    public function returnResponse($action, $response)
    {
        $respClass = "TencentCloud"."\\".ucfirst("fmu")."\\"."V20191213\\Models"."\\".ucfirst($action)."Response";
        $obj = new $respClass();
        $obj->deserialize($response);
        return $obj;
    }
}
