<?php
/**
 * Created by PhpStorm.
 * User: ZTB
 * Date: 2018/1/23
 * Time: 18:10
 */

namespace Video\Controller;

use Oss\Service\AliOssService;
use Think\Controller;
use Video\Service\STSService;
use Video\Service\VideoService;

class STSController extends Controller {

    protected $AK = '';
    protected $SK = '';
    protected $AM = '';


    public function _initialize() {
        header("Content-type:text/html;charset=utf-8");
        //todo 获取后台配置的 ak 等信息
        $this->AK = '';
        $this->SK = '';
        $this->AM = '';
    }

    /**
     * 获取播放凭证
     */
    function getPlayAuth() {
        try {
            $videoId  = I('VideoId');
            $client   = VideoService::getVodClient($this->AK, $this->SK);
            $playInfo = VideoService::getPlayAuth($client, $videoId);
            $this->ajaxReturn($playInfo);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * 播放
     */
    function play() {
        try {
            $videoId  = I('VideoId');
            $client   = VideoService::getVodClient($this->AK, $this->SK);
            $playInfo = VideoService::getPlayInfo($client, $videoId);
            $this->ajaxReturn($playInfo);
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * 获取视频列表
     */
    function getVideoList() {
        //参数
        $page  = I('page', 1);
        $limit = I('limit', 20);
        $catid = I('catid');

        $client = STSService::getClient($this->AK, $this->SK, $this->AM);
        $this->ajaxReturn(STSService::getVideoList($client, $page, $limit, $catid));
    }

    /**
     * 视频信息
     */
    function getVideoInfo() {
        try {
            $videoId = I('VideoId');
            $client   = VideoService::getVodClient($this->AK, $this->SK);
            $playInfo = VideoService::getVideoInfo($client, $videoId);
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
            $title = I('title');
            $file  = I('file');

            $client = STSService::getClient($this->AK, $this->SK, $this->AM);
            $this->ajaxReturn(VideoService::createUploadVideo($client, $title, $file));
        }
    }

    /**
     * 刷新上传视频凭证
     */
    function refreshUploadVideo() {
        if (IS_POST) {
            $videoId = I('videoId');

            $client = STSService::getClient($this->AK, $this->SK, $this->AM);
            $this->ajaxReturn(VideoService::refreshUploadVideo($client, $videoId));
        }
    }

    /**
     * 上传视频
     */
    function upload() {
        $uploadAuth    = I('uploadAuth');
        $uploadAddress = I('uploadAddress');
        $file          = I('file');
        $res           = VideoService::upload($uploadAddress, $uploadAuth, $file);
        $this->ajaxReturn($res);
    }

    /**
     * 删除视频
     */
    function delVideos() {
        $videoId = I('VideoId');
        $client  = STSService::getClient($this->AK, $this->SK, $this->AM);
        $res     = VideoService::deleteVideos($client, $videoId);
        $this->ajaxReturn($res);
    }
}