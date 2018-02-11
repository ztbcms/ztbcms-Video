<?php
/**
 * Created by PhpStorm.
 * User: ZTB
 * Date: 2018/1/23
 * Time: 18:14
 */

namespace Video\Service;

require_once dirname(__DIR__) . '/Lib/aliyun-php-sdk-core/Config.php';

use DefaultProfile as DefaultProfile;
use DefaultAcsClient as DefaultAcsClient;
use Sts\Request\V20150401 as Sts;

class STSService {

    /**
     * 获取角色授权
     *
     * @param        $AK
     * @param        $SK
     * @param        $roleArn
     * @param        $roleName
     * @param string $policy 附加策略
     * @param int    $expire 过期时间
     * @param string $dataType
     *
     * @return \Aliyun\Core\Http\HttpResponse
     */
    static function getSTSAuth($AK, $SK, $roleArn, $roleName = '', $policy = '', $expire = 1800, $dataType = 'JSON') {
        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $AK, $SK);
        $client         = new DefaultAcsClient($iClientProfile);
        $request        = new Sts\AssumeRoleRequest();
        $request->setRoleSessionName($roleName);
        $request->setRoleArn($roleArn);
        $request->setDurationSeconds($expire);
        $request->setAcceptFormat($dataType);

        if ($policy) {
            $request->setPolicy($policy);
        }

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

    /**
     * 获取client
     *
     * @param $AK
     * @param $SK
     * @param $AM
     * @param $roleName
     * @param $policy
     * @param $expire
     *
     * @return array|\DefaultAcsClient
     */
    static function getClient($AK, $SK, $AM, $roleName = 'videoAdmin', $policy = [], $expire = 1800) {

        $profile = STSService::getSTSAuth($AK, $SK, $AM, $roleName, $policy, $expire);

        if ($profile->getStatus()) {

            $body = json_decode($profile->getBody(), 1);

            $STS_AK        = $body[ 'Credentials' ][ 'AccessKeyId' ];
            $STS_SK        = $body[ 'Credentials' ][ 'AccessKeySecret' ];
            $SecurityToken = $body[ 'Credentials' ][ 'SecurityToken' ];

            return VideoService::getVodClient($STS_AK, $STS_SK, $SecurityToken);
        } else {
            return createReturn(false, [], 'STS 授权失败!');
        }
    }

    /**
     * STS 方式获取视频列表
     *
     * @param $client
     * @param $page
     * @param $limit
     * @param $catid
     *
     * @return array|mixed
     */
    static function getVideoList($client, $page, $limit, $catid = 0) {
        return VideoService::getVideoList($client, $page, $limit, $catid);
    }
}