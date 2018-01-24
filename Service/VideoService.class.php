<?php
/**
 * Created by PhpStorm.
 * User: ZTB
 * Date: 2018/1/23
 * Time: 18:14
 */

namespace Video\Service;

require_once dirname(__DIR__) . '/Lib/aliyun-php-sdk-core/Config.php';

use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use vod\Request\V20170321 as vod;
use Sts\Request\V20150401 as Sts;

class VideoService {

    /**
     * 初始化视频服务客户端
     *
     * @param $accessKeyId
     * @param $accessKeySecret
     *
     * @return \Aliyun\Core\DefaultAcsClient
     */
    static function init_vod_client($accessKeyId, $accessKeySecret) {
        $regionId = 'cn-shanghai';  // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域
        $profile  = DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret);
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
    static function get_play_auth($client, $videoId, $expire = 1800, $dataType = 'JSON') {
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
    static function get_play_info($client, $videoId, $dataType = 'JSON') {
        $request = new vod\GetPlayInfoRequest();
        $request->setVideoId($videoId);
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
    }

    /**
     * 上传视频
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
    static function create_upload_video($client, $title, $fileName, $desc = '视频描述', $url = '', $tags = '', $dataType = 'JSON') {
        $request = new vod\CreateUploadVideoRequest();
        $request->setTitle($title);        // 视频标题(必填参数)
        $request->setFileName($fileName); // 视频源文件名称，必须包含扩展名(必填参数)
        $request->setDescription($desc);  // 视频源文件描述(可选)
        $request->setCoverURL($url); // 自定义视频封面(可选)
        $request->setTags($tags); // 视频标签，多个用逗号分隔(可选)
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
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
    static function get_video_info($client, $videoId, $dataType = 'JSON') {
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
    static function update_video_info($client, $videoId, $title, $desc, $coverUrl, $tags = '', $catid = 0, $dataType = 'JSON') {
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
    static function delete_videos($client, $videoIds, $dataType = 'JSON') {
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
     *
     * @return mixed
     */
    static function get_mezzanine_info($client, $videoId, $expire = '1800', $dataType = 'JSON') {
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
     * @param $dataType
     *
     * @return mixed
     */
    static function get_video_list($client, $dataType = 'JSON') {
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
        #$request->setCateId(0);               // 按分类进行筛选
        $request->setPageNo(1);
        $request->setPageSize(20);
        $request->setAcceptFormat($dataType);
        return $client->getAcsResponse($request);
    }

    /**
     * 从阿里云OSS获取视频资源
     */
    static function getVideoFromOSS() {

    }

    /**
     * @param        $AK
     * @param        $SK
     * @param        $roleArn
     * @param        $clientName
     * @param string $policy 附加策略
     *
     * @return \Aliyun\Core\Http\HttpResponse
     */
    static function getSTSAuth($AK, $SK, $roleArn, $clientName = '', $policy = '') {
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $AK, $SK);
        $client         = new DefaultAcsClient($iClientProfile);
        $request        = new Sts\AssumeRoleRequest();
        $request->setRoleSessionName($clientName);
        $request->setRoleArn($roleArn);
        $request->setDurationSeconds(3600);

        if ($policy) {
            $request->setPolicy($policy);
        }

        return $client->doAction($request);
    }

    /**
     * 获取当前请求身份
     *
     * @param $AK
     * @param $SK
     * @param $dataType
     *
     * @return \Aliyun\Core\Http\HttpResponse
     */
    static function getCallerIdentity($AK, $SK, $dataType = 'JSON') {
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $AK, $SK);
        $client         = new DefaultAcsClient($iClientProfile);
        $request        = new Sts\GetCallerIdentityRequest();
        $request->setAcceptFormat($dataType);

        /* 返回示例
         {
            "Credentials": {
                "AccessKeyId": "STS.L4aBSCSJVMuKg5U1vFDw",
                "AccessKeySecret": "wyLTSmsyPGP1ohvvw8xYgB29dlGI8KMiH2pKCNZ9",
                "Expiration": "2015-04-09T11:52:19Z",
                "SecurityToken": "CAESrAIIARKAAShQquMnLIlbvEcIxO6wCoqJufs8sWwieUxu45hS9AvKNEte8KRUWiJWJ6Y+YHAPgNwi7yfRecMFydL2uPOgBI7LDio0RkbYLmJfIxHM2nGBPdml7kYEOXmJp2aDhbvvwVYIyt/8iES/R6N208wQh0Pk2bu+/9dvalp6wOHF4gkFGhhTVFMuTDRhQlNDU0pWTXVLZzVVMXZGRHciBTQzMjc0KgVhbGljZTCpnJjwySk6BlJzYU1ENUJuCgExGmkKBUFsbG93Eh8KDEFjdGlvbkVxdWFscxIGQWN0aW9uGgcKBW9zczoqEj8KDlJlc291cmNlRXF1YWxzEghSZXNvdXJjZRojCiFhY3M6b3NzOio6NDMyNzQ6c2FtcGxlYm94L2FsaWNlLyo="
            },
            "AssumedRoleUser": {
                "arn": "acs:sts::1234567890123456:assumed-role/AdminRole/alice",
                "AssumedRoleUserId":"344584339364951186:alice"
            },
            "RequestId": "6894B13B-6D71-4EF5-88FA-F32781734A7F"
            }
         */
        return $client->doAction($request);
    }
}