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
namespace TencentCloud\Kms\V20190118\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getKeyId() 获取CMK的全局唯一标识
 * @method void setKeyId(string $KeyId) 设置CMK的全局唯一标识
 * @method string getPlaintext() 获取生成的DataKey的明文，该明文使用base64编码，用户需要使用base64解码得到明文
 * @method void setPlaintext(string $Plaintext) 设置生成的DataKey的明文，该明文使用base64编码，用户需要使用base64解码得到明文
 * @method string getCiphertextBlob() 获取DataKey加密后经过base64编码的密文，用户需要自行保存密文
 * @method void setCiphertextBlob(string $CiphertextBlob) 设置DataKey加密后经过base64编码的密文，用户需要自行保存密文
 * @method string getRequestId() 获取唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 * @method void setRequestId(string $RequestId) 设置唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
 */

/**
 *GenerateDataKey返回参数结构体
 */
class GenerateDataKeyResponse extends AbstractModel
{
    /**
     * @var string CMK的全局唯一标识
     */
    public $KeyId;

    /**
     * @var string 生成的DataKey的明文，该明文使用base64编码，用户需要使用base64解码得到明文
     */
    public $Plaintext;

    /**
     * @var string DataKey加密后经过base64编码的密文，用户需要自行保存密文
     */
    public $CiphertextBlob;

    /**
     * @var string 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    public $RequestId;
    /**
     * @param string $KeyId CMK的全局唯一标识
     * @param string $Plaintext 生成的DataKey的明文，该明文使用base64编码，用户需要使用base64解码得到明文
     * @param string $CiphertextBlob DataKey加密后经过base64编码的密文，用户需要自行保存密文
     * @param string $RequestId 唯一请求 ID，每次请求都会返回。定位问题时需要提供该次请求的 RequestId。
     */
    function __construct()
    {

    }
    /**
     * For internal only. DO NOT USE IT.
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("KeyId",$param) and $param["KeyId"] !== null) {
            $this->KeyId = $param["KeyId"];
        }

        if (array_key_exists("Plaintext",$param) and $param["Plaintext"] !== null) {
            $this->Plaintext = $param["Plaintext"];
        }

        if (array_key_exists("CiphertextBlob",$param) and $param["CiphertextBlob"] !== null) {
            $this->CiphertextBlob = $param["CiphertextBlob"];
        }

        if (array_key_exists("RequestId",$param) and $param["RequestId"] !== null) {
            $this->RequestId = $param["RequestId"];
        }
    }
}
