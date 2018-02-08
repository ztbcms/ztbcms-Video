<?php
/**
 * Created by PhpStorm.
 * User: ZTB
 * Date: 2018/1/25
 * Time: 14:56
 */

class VideoTest extends PHPUnit_Framework_TestCase {

    /**
     * 测试获取客户端方法
     */
    public function testGetClient() {
        $this->assertInstanceOf(
            DefaultAcsClient::class,
            \Video\Service\VideoService::getVodClient('1', '2')
        );
    }


}