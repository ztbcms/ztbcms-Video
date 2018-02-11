<?php
/**
 * Created by PhpStorm.
 * User: ZTB
 * Date: 2018/1/23
 * Time: 18:10
 */

namespace Video\Controller;

use OSS\Core\OssException;
use OSS\OssClient;
use Oss\Service\AliOssService;
use Think\Controller;
use Video\Service\VideoService;

class VideoController extends Controller {

    protected $AK = '';
    protected $SK = '';

    public function _initialize() {
        header("Content-type:text/html;charset=utf-8");
        //todo 获取后台配置的 ak 等信息
        $this->AK = '';
        $this->SK = '';
    }

    /**
     * 视频信息
     */
    function getVideoInfo() {
        try {
            $client   = VideoService::getVodClient($this->AK, $this->SK);
            $playInfo = VideoService::getVideoInfo($client, 'e5877edcc26f41f49626b151e1ad8a27');
            var_dump($playInfo);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * 播放
     */
    function play() {
        try {
            $client   = VideoService::getVodClient($this->AK, $this->SK);
            $playInfo = VideoService::getPlayInfo($client, 'bf72d4fbe3354bc6981ebe2ca4634214');
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
            $client   = VideoService::getVodClient($this->AK, $this->SK);
            $playInfo = VideoService::getPlayAuth($client, 'bf72d4fbe3354bc6981ebe2ca4634214');
            var_dump($playInfo);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * 创建上传视频凭证
     */
    function createUploadVideo() {
        if (IS_POST) {
            $title    = I('title');
            $fileNmae = I('fileNmae');

            $client = VideoService::getVodClient($this->AK, $this->SK);
            $res    = VideoService::createUploadVideo($client, $title, $fileNmae);
            $this->ajaxReturn($res);
        }
    }

    /**
     * 上传视频文件
     */
    public function upload() {
        $uploadAuth    = I('uploadAuth');
        $uploadAddress = I('uploadAddress');
        $file          = I('file');
        $res           = VideoService::upload($uploadAddress, $uploadAuth, $file);
        $this->ajaxReturn($res);
    }

    /**
     * 刷新上传视频凭证
     */
    function refreshUploadVideo() {
        if (IS_POST) {
            $videoId = I('videoId');

            $client = VideoService::getVodClient($this->AK, $this->SK);
            $res    = VideoService::refreshUploadVideo($client, $videoId);
            $this->ajaxReturn($res);
        }
    }

    /**
     * 获取视频列表
     */
    function getVideoList() {
        $page   = I('page', 1);
        $limit  = I('limit', 20);
        $catid  = I('catid');
        $client = VideoService::getVodClient($this->AK, $this->SK);
        $res    = VideoService::getVideoList($client, $page, $limit, $catid);
        $this->ajaxReturn($res);
    }

    /**
     * 删除视频
     */
    function delVideos() {
        $videoId = I('VideoId');
        $client  = VideoService::getVodClient($this->AK, $this->SK);
        $res     = VideoService::deleteVideos($client, $videoId);
        $this->ajaxReturn($res);
    }
}