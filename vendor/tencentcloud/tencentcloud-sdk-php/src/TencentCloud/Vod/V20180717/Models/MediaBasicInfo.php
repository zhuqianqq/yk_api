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
namespace TencentCloud\Vod\V20180717\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getName() 获取媒体文件名称。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setName(string $Name) 设置媒体文件名称。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getDescription() 获取媒体文件描述。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setDescription(string $Description) 设置媒体文件描述。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getCreateTime() 获取媒体文件的创建时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setCreateTime(string $CreateTime) 设置媒体文件的创建时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getUpdateTime() 获取媒体文件的最近更新时间（如修改视频属性、发起视频处理等会触发更新媒体文件信息的操作），使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setUpdateTime(string $UpdateTime) 设置媒体文件的最近更新时间（如修改视频属性、发起视频处理等会触发更新媒体文件信息的操作），使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getExpireTime() 获取媒体文件的过期时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。过期后该媒体文件及其相关资源（转码结果、雪碧图等）将被永久删除。“9999-12-31T23:59:59Z”表示永不过期。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setExpireTime(string $ExpireTime) 设置媒体文件的过期时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。过期后该媒体文件及其相关资源（转码结果、雪碧图等）将被永久删除。“9999-12-31T23:59:59Z”表示永不过期。
注意：此字段可能返回 null，表示取不到有效值。
 * @method integer getClassId() 获取媒体文件的分类 ID。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setClassId(integer $ClassId) 设置媒体文件的分类 ID。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getClassName() 获取媒体文件的分类名称。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setClassName(string $ClassName) 设置媒体文件的分类名称。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getClassPath() 获取媒体文件的分类路径，分类间以“-”分隔，如“新的一级分类 - 新的二级分类”。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setClassPath(string $ClassPath) 设置媒体文件的分类路径，分类间以“-”分隔，如“新的一级分类 - 新的二级分类”。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getCoverUrl() 获取媒体文件的封面图片地址。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setCoverUrl(string $CoverUrl) 设置媒体文件的封面图片地址。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getType() 获取媒体文件的封装格式，例如 mp4、flv 等。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setType(string $Type) 设置媒体文件的封装格式，例如 mp4、flv 等。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getMediaUrl() 获取原始媒体文件的 URL 地址。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setMediaUrl(string $MediaUrl) 设置原始媒体文件的 URL 地址。
注意：此字段可能返回 null，表示取不到有效值。
 * @method MediaSourceData getSourceInfo() 获取该媒体文件的来源信息。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setSourceInfo(MediaSourceData $SourceInfo) 设置该媒体文件的来源信息。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getStorageRegion() 获取媒体文件存储地区，如 ap-guangzhou，参见[地域列表](https://cloud.tencent.com/document/api/213/15692#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8)。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setStorageRegion(string $StorageRegion) 设置媒体文件存储地区，如 ap-guangzhou，参见[地域列表](https://cloud.tencent.com/document/api/213/15692#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8)。
注意：此字段可能返回 null，表示取不到有效值。
 * @method array getTagSet() 获取媒体文件的标签信息。
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setTagSet(array $TagSet) 设置媒体文件的标签信息。
注意：此字段可能返回 null，表示取不到有效值。
 * @method string getVid() 获取直播录制文件的唯一标识
注意：此字段可能返回 null，表示取不到有效值。
 * @method void setVid(string $Vid) 设置直播录制文件的唯一标识
注意：此字段可能返回 null，表示取不到有效值。
 */

/**
 *点播媒体文件基础信息
 */
class MediaBasicInfo extends AbstractModel
{
    /**
     * @var string 媒体文件名称。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Name;

    /**
     * @var string 媒体文件描述。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Description;

    /**
     * @var string 媒体文件的创建时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $CreateTime;

    /**
     * @var string 媒体文件的最近更新时间（如修改视频属性、发起视频处理等会触发更新媒体文件信息的操作），使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $UpdateTime;

    /**
     * @var string 媒体文件的过期时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。过期后该媒体文件及其相关资源（转码结果、雪碧图等）将被永久删除。“9999-12-31T23:59:59Z”表示永不过期。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ExpireTime;

    /**
     * @var integer 媒体文件的分类 ID。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ClassId;

    /**
     * @var string 媒体文件的分类名称。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ClassName;

    /**
     * @var string 媒体文件的分类路径，分类间以“-”分隔，如“新的一级分类 - 新的二级分类”。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $ClassPath;

    /**
     * @var string 媒体文件的封面图片地址。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $CoverUrl;

    /**
     * @var string 媒体文件的封装格式，例如 mp4、flv 等。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Type;

    /**
     * @var string 原始媒体文件的 URL 地址。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $MediaUrl;

    /**
     * @var MediaSourceData 该媒体文件的来源信息。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $SourceInfo;

    /**
     * @var string 媒体文件存储地区，如 ap-guangzhou，参见[地域列表](https://cloud.tencent.com/document/api/213/15692#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8)。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $StorageRegion;

    /**
     * @var array 媒体文件的标签信息。
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $TagSet;

    /**
     * @var string 直播录制文件的唯一标识
注意：此字段可能返回 null，表示取不到有效值。
     */
    public $Vid;
    /**
     * @param string $Name 媒体文件名称。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $Description 媒体文件描述。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $CreateTime 媒体文件的创建时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $UpdateTime 媒体文件的最近更新时间（如修改视频属性、发起视频处理等会触发更新媒体文件信息的操作），使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $ExpireTime 媒体文件的过期时间，使用 [ISO 日期格式](https://cloud.tencent.com/document/product/266/11732#I)。过期后该媒体文件及其相关资源（转码结果、雪碧图等）将被永久删除。“9999-12-31T23:59:59Z”表示永不过期。
注意：此字段可能返回 null，表示取不到有效值。
     * @param integer $ClassId 媒体文件的分类 ID。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $ClassName 媒体文件的分类名称。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $ClassPath 媒体文件的分类路径，分类间以“-”分隔，如“新的一级分类 - 新的二级分类”。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $CoverUrl 媒体文件的封面图片地址。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $Type 媒体文件的封装格式，例如 mp4、flv 等。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $MediaUrl 原始媒体文件的 URL 地址。
注意：此字段可能返回 null，表示取不到有效值。
     * @param MediaSourceData $SourceInfo 该媒体文件的来源信息。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $StorageRegion 媒体文件存储地区，如 ap-guangzhou，参见[地域列表](https://cloud.tencent.com/document/api/213/15692#.E5.9C.B0.E5.9F.9F.E5.88.97.E8.A1.A8)。
注意：此字段可能返回 null，表示取不到有效值。
     * @param array $TagSet 媒体文件的标签信息。
注意：此字段可能返回 null，表示取不到有效值。
     * @param string $Vid 直播录制文件的唯一标识
注意：此字段可能返回 null，表示取不到有效值。
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
        if (array_key_exists("Name",$param) and $param["Name"] !== null) {
            $this->Name = $param["Name"];
        }

        if (array_key_exists("Description",$param) and $param["Description"] !== null) {
            $this->Description = $param["Description"];
        }

        if (array_key_exists("CreateTime",$param) and $param["CreateTime"] !== null) {
            $this->CreateTime = $param["CreateTime"];
        }

        if (array_key_exists("UpdateTime",$param) and $param["UpdateTime"] !== null) {
            $this->UpdateTime = $param["UpdateTime"];
        }

        if (array_key_exists("ExpireTime",$param) and $param["ExpireTime"] !== null) {
            $this->ExpireTime = $param["ExpireTime"];
        }

        if (array_key_exists("ClassId",$param) and $param["ClassId"] !== null) {
            $this->ClassId = $param["ClassId"];
        }

        if (array_key_exists("ClassName",$param) and $param["ClassName"] !== null) {
            $this->ClassName = $param["ClassName"];
        }

        if (array_key_exists("ClassPath",$param) and $param["ClassPath"] !== null) {
            $this->ClassPath = $param["ClassPath"];
        }

        if (array_key_exists("CoverUrl",$param) and $param["CoverUrl"] !== null) {
            $this->CoverUrl = $param["CoverUrl"];
        }

        if (array_key_exists("Type",$param) and $param["Type"] !== null) {
            $this->Type = $param["Type"];
        }

        if (array_key_exists("MediaUrl",$param) and $param["MediaUrl"] !== null) {
            $this->MediaUrl = $param["MediaUrl"];
        }

        if (array_key_exists("SourceInfo",$param) and $param["SourceInfo"] !== null) {
            $this->SourceInfo = new MediaSourceData();
            $this->SourceInfo->deserialize($param["SourceInfo"]);
        }

        if (array_key_exists("StorageRegion",$param) and $param["StorageRegion"] !== null) {
            $this->StorageRegion = $param["StorageRegion"];
        }

        if (array_key_exists("TagSet",$param) and $param["TagSet"] !== null) {
            $this->TagSet = $param["TagSet"];
        }

        if (array_key_exists("Vid",$param) and $param["Vid"] !== null) {
            $this->Vid = $param["Vid"];
        }
    }
}
