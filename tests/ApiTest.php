<?php

// declare (strict_types = 1);

namespace Euraka\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @covers Euraka API测试
 */
class ApiTest extends TestCase {

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var ResponseInterface
     */
    private $response;

    private $client;

    public $appId      = 'eureka_demo';
    public $instanceId = '007';

    public $host    = 'http://127.0.0.1';
    public $port    = '8008';
    public $context = 'eureka';

    public function setUp() {
        $this->client = new \Euraka\Client($this->host, $this->port, $this->context);

        $this->httpClient = $this->getMockBuilder('GuzzleHttp\Client')
            ->setMethods(['request'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->setMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->response->expects($this->any())
            ->method('getBody')
            ->willReturn('{}');
    }

    protected function getEurekaUri() {
        return $this->host . ':' . $this->port . '/' . $this->context;
    }

    /**
     * Test EurekaClient::registerApp() method.
     */
    public function testRegisterApp() {

        $config = $this->client->getDefaultConfig($this->appId, $this->instanceId);

        $data = $this->httpClient->expects($this->once())
            ->method('request')
            ->with('POST', $this->getEurekaUri() . '/apps/' . $this->appId, [
                'json' => [
                    'instance' => $config,
                ],
            ]);

        $data = $this->client->registerApp($this->appId, $this->instanceId);
    }

    public function testGetAllApps() {
        $list = $this->client->getAllApps();
    }

    /**
     * Test EurekaClient::deRegisterApp() method.
     */
    public function testDeRegisterApp() {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('DELETE', $this->getEurekaUri() . '/apps/' . $this->appId . '/' . $this->instanceId);

        $this->client->deRegisterApp($this->appId, $this->instanceId);
    }

}