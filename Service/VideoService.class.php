<?php
/**
 * Created by PhpStorm.
 * User: ZTB
 * Date: 2018/1/23
 * Time: 18:14
 */

namespace Video\Service;

require_once dirname(__DIR__) . '/Lib/aliyun-php-sdk-core/Config.php';
require_once dirname(dirname(__DIR__)) . '/Oss/Lib/AliyunOss/autoload.php';

use DefaultProfile as DefaultProfile;
use DefaultAcsClient as DefaultAcsClient;
use OSS\Core\OssException;
use OSS\OssClient;
use vod\Request\V20170321 as vod;

class VideoService {

    /**
     * 初始化视频服务客户端
     *
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $SecurityToken
     *
     * @return DefaultAcsClient
     */
    static function getVodClient($accessKeyId, $accessKeySecret, $SecurityToken = null) {
        $regionId = 'cn-shanghai';  // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域
        $profile  = DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret, $SecurityToken);
        return new DefaultAcsClient($profile);
    }

    /**
     * 获取播放凭证
     *
     * @param $client  DefaultAcsClient 视频服务客户端
     * @param $videoId string 视频ID
     * @param $expire  int 过期时间
     * @param $dataType
     *
     * @return mixed
     */
    static function getPlayAuth($client, $videoId, $expire = 1800, $dataType = 'JSON') {
        $request = new vod\GetVideoPlayAuthRequest();
        $request->setVideoId($videoId);
        $request->setAuthInfoTimeout($expire);
        $request->setAcceptFormat($dataType);
        $response = $client->getAcsResponse($request);
        return $response;
    }

    /**
     * 获取视频播放信息
     *
     * @param $client
     * @param $videoId
     * @param $dataType
     *
     * @return mixed
     */
    static function getPlayInfo($client, $videoId, $dataType = 'JSON') {
        $request = new vod\GetPlayInfoRequest();
        $request->setVideoId($videoId);
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
    }

    /**
     * 获取视频上传凭证
     *
     * @param        $client
     * @param        $title
     * @param        $fileName
     * @param string $desc
     * @param string $url
     * @param string $tags
     * @param string $dataType
     *
     * @return mixed
     */
    static function createUploadVideo($client, $title, $fileName, $desc = '视频描述', $url = '', $tags = '', $dataType = 'JSON') {
        try {
            $request = new vod\CreateUploadVideoRequest();
            $request->setTitle($title);        // 视频标题(必填参数)
            $request->setFileName($fileName); // 视频源文件名称，必须包含扩展名(必填参数)
            $request->setDescription($desc);  // 视频源文件描述(可选)
            $request->setCoverURL($url); // 自定义视频封面(可选)
            $request->setTags($tags); // 视频标签，多个用逗号分隔(可选)
            $request->setAcceptFormat($dataType);

            /* 返回示例
                {
                 "RequestId": "25818875-5F78-4A13-BEF6-D7393642CA58",
                 "VideoId": "93ab850b4f6f44eab54b6e91d24d81d4",
                 "UploadAddress": "eyJTZWN1cml0eVRva2VuIjoiQ0FJU3p3TjF",
                 "UploadAuth": "eyJFbmRwb2ludCI6Im"
                }
             */
            $res = $client->getAcsResponse($request);
            return createReturn(true, $res, '创建成功');
        } catch (\Exception $e) {
            switch ($e->getHttpStatus()) {
                case '400':
                    return createReturn(false, [], '文件不存在', '400');
                case '403':
                    return createReturn(false, [], '服务开通时账号初始化失败', '403');
                case '404':
                    return createReturn(false, [], '指定的模板组ID不存在', '404');
                case '503':
                    return createReturn(false, [], '创建视频信息失败，请稍后重试', '503');
            }
        }
    }

    static function refreshUploadVideo($client, $videoId) {
        $request = new vod\RefreshUploadVideoRequest();
        $request->setVideoId($videoId);

        /* 返回示例
           {
            "RequestId": "25818875-5F78-4A13-BEF6-D7393642CA58",
            "VideoId": "93ab850b4f6f44eab54b6e91d24d81d4",
            "UploadAddress": "eyJTZWN1cml0eVRva2VuIjoiQ0FJU3p3TjF",
            "UploadAuth": "eyJFbmRwb2ludCI6Im"
           }
        */
        $res = $client->getAcsResponse($request);
        return createReturn(true, $res, '更新成功');
    }

    /**
     * 获取视频信息
     *
     * @param $client
     * @param $videoId
     * @param $dataType
     *
     * @return mixed
     */
    static function getVideoInfo($client, $videoId, $dataType = 'JSON') {
        $request = new vod\GetVideoInfoRequest();
        $request->setVideoId($videoId);
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
    }

    /**
     * 修改视频信息
     *
     * @param        $client
     * @param        $videoId
     * @param        $title
     * @param        $desc
     * @param        $coverUrl
     * @param string $tags
     * @param int    $catid
     * @param string $dataType
     *
     * @return mixed
     */
    static function updateVideoInfo($client, $videoId, $title, $desc, $coverUrl, $tags = '', $catid = 0, $dataType = 'JSON') {
        $request = new vod\UpdateVideoInfoRequest();
        $request->setVideoId($videoId);
        $request->setTitle($title);   // 更改视频标题
        $request->setDescription($desc);    // 更改视频描述
        $request->setCoverURL($coverUrl);  // 更改视频封面
        $request->setTags($tags);    // 更改视频标签，多个用逗号分隔
        $request->setCateId($catid);       // 更改视频分类(可在点播控制台·全局设置·分类管理里查看分类ID：https://vod.console.aliyun.com/#/vod/settings/category)
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
    }

    /**
     * 删除视频
     *
     * @param        $client
     * @param        $videoIds
     * @param string $dataType
     *
     * @return mixed
     */
    static function deleteVideos($client, $videoIds, $dataType = 'JSON') {
        $request = new vod\DeleteVideoRequest();
        $request->setVideoIds($videoIds);   // 支持批量删除视频；videoIds为传入的视频ID列表，多个用逗号分隔
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
    }

    /**
     * 获取源文件地址
     *
     * @param $client
     * @param $videoId
     * @param $expire
     * @param $dataType
     *
     * @return mixed
     */
    static function getMezzanineInfo($client, $videoId, $expire = '1800', $dataType = 'JSON') {
        $request = new vod\GetMezzanineInfoRequest();
        $request->setVideoId($videoId);
        $request->setAuthTimeout($expire);   // 原片下载地址过期时间，单位：秒，默认为3600秒
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
    }

    /**
     * 获取视频列表
     *
     * @param $client
     * @param $page
     * @param $limit
     * @param $catid
     * @param $dataType
     *
     * @return mixed
     */
    static function getVideoList($client, $page = 1, $limit = 20, $catid = 0, $dataType = 'JSON') {
        try {
            $request = new vod\GetVideoListRequest();
            // 示例：分别取一个月前、当前时间的UTC时间作为筛选视频列表的起止时间
            $localTimeZone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $utcNow      = gmdate('Y-m-d\TH:i:s\Z');
            $utcMonthAgo = gmdate('Y-m-d\TH:i:s\Z', time() - 30 * 86400);
            date_default_timezone_set($localTimeZone);
            $request->setStartTime($utcMonthAgo);   // 视频创建的起始时间，为UTC格式
            $request->setEndTime($utcNow);          // 视频创建的结束时间，为UTC格式
            #$request->setStatus('Uploading,Normal,Transcoding');  // 视频状态，默认获取所有状态的视频，多个用逗号分隔
            if ($catid) $request->setCateId($catid);
            $request->setPageNo($page);
            $request->setPageSize($limit);
            $request->setAcceptFormat($dataType);
            $res  = $client->getAcsResponse($request);
            $data = [
                'page'        => $page,
                'limit'       => $limit,
                'lists'       => $res->{'VideoList'}
                    ->{
                    'Video'},
                'total'       => $res->{'Total'},
                'total_pages' => ceil($res->{'Total'} / $limit),
            ];

            return createReturn(true, $data);
        } catch (\Exception $e) {
            switch ($e->getHttpStatus()) {
                case '404':
                    return createReturn(false, [], '视频列表为空', '404');
                case '400':
                    return createReturn(false, [], '翻页总条数超过最大限制', '400');
            }
        }
    }

    /**
     * 上传视频文件
     *
     * @param $uploadAddress string 视频上传授权地址
     * @param $uploadAuth    string 视频上传授权令牌
     * @param $file          string 本地文件
     *
     * @return array
     */
    static function upload($uploadAddress, $uploadAuth, $file) {
        $address  = json_decode(base64_decode($uploadAddress), true);
        $bucket   = $address[ 'Bucket' ];
        $endpoint = $address[ 'Endpoint' ];
        $fileName = $address[ 'FileName' ];

        $auth            = json_decode(base64_decode($uploadAuth), true);
        $accessKeyId     = $auth[ 'AccessKeyId' ];
        $accessKeySecret = $auth[ 'AccessKeySecret' ];
        $expire          = $auth[ 'Expiration' ];
        $securityToken   = $auth[ 'SecurityToken' ];

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $securityToken);
            $ossClient->uploadFile($bucket, $fileName, $file);

            return createReturn(true, [], '上传成功');

        } catch (OssException $e) {
            return createReturn(false, [], $e->getMessage());
        }
    }
}