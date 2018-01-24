<?php
/**
 * Created by PhpStorm.
 * User: ZTB
 * Date: 2018/1/23
 * Time: 18:10
 */

namespace Video\Controller;

use Think\Controller;
use Video\Service\VideoService;

class TestController extends Controller {

    protected $AK = '';
    protected $SK = '';


    function STS() {

    }

    /**
     * 播放
     */
    function play() {
        try {
            $client   = VideoService::init_vod_client($this->AK, $this->SK);
            $playInfo = VideoService::get_play_info($client, '您的videoId');
            var_dump($playInfo);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * 获取播放凭证
     */
    function getPlayAuth() {
        try {
            $client   = VideoService::init_vod_client($this->AK, $this->SK);
            $playInfo = VideoService::get_play_auth($client, '您的videoId');
            var_dump($playInfo);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * 上传
     */
    function upload() {
        try {
            $client     = VideoService::init_vod_client($this->AK, $this->SK);
            $uploadInfo = VideoService::create_upload_video($client, '视频', '1.vod');
            var_dump($uploadInfo);
        } catch (\Exception $e) {
            print $e->getMessage() . "\n";
        }
    }


}