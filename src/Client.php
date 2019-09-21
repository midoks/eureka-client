<?php

namespace Euraka;

use GuzzleHttp\Client as GClient;

// 与eureka服务端rest交互
// https://github.com/Netflix/eureka/wiki/Eureka-REST-operations

class Client {

    const VERSION = '0.1.0';

    public $host;
    public $port;
    private $context;

    private $client = NULL;

    public function __construct($host = 'http://127.0.0.1', $port = '8176', $context = 'eureka/v2') {

        $this->host    = $host;
        $this->port    = $port;
        $this->context = $context;

        $this->client = new GClient(['base_uri' => $host . ':' . $port]);
    }

    /**
     * @return string
     */
    protected function getEurekaUri() {
        return $this->host . ':' . $this->port . '/' . $this->context;
    }

    public function getDefaultConfig($appId, $instanceId) {
        $config = [
            'eurekaDefaultUrl'              => 'http://127.0.0.1:8008/eureka',
            'hostName'                      => 'localhost',
            'app'                           => $appId,
            'ip'                            => '127.0.0.1',
            'ipAddr'                        => '127.0.0.1',
            'port'                          => [
                '$'        => 8888,
                '@enabled' => true,
            ],
            'securePort'                    => [
                '$'        => 443,
                '@enabled' => true,
            ],
            'homePageUrl'                   => 'http://localhost:8888',
            'statusPageUrl'                 => 'http://localhost:8888/info',
            'healthCheckUrl'                => 'http://localhost:8888/health',
            'dataCenterInfo'                => [
                'name'   => 'MyOwn',
                '@class' => 'com.netflix.appinfo.MyDataCenterInfo',
            ],
            'metadata'                      => [
                '@class' => '',
            ],
            'isCoordinatingDiscoveryServer' => 'false',
            'lastUpdatedTimestamp'          => (string) (round(microtime(true) * 1000)),

            'instanceId'                    => $instanceId,
            'actionType'                    => "ADDED",
            'metadata'                      => ['demo' => 'test'],
        ];
        return $config;
    }

    public function registerApp($appId, $instanceId, $config = []) {
        if (empty($config)) {
            $config = $this->getDefaultConfig($appId, $instanceId);
        }

        $res = $this->client->request('POST', $this->getEurekaUri() . '/apps/' . $appId, [
            'json' => [
                'instance' => $config,
            ],
        ]);
        return $res;
    }

    public function deRegisterApp($appId, $instanceId) {
        return $this->client->request('DELETE', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId);
    }

    public function getApp($appId) {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/apps/' . $appId, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    public function getAllApps() {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/apps', [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    public function heartBeat($appId, $instanceId) {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId);
    }

    /**
     * Get application Instance.
     *
     * @param string $appId
     * @param string $instanceId
     *
     * @throws GuzzleException
     *
     * @return array
     */
    public function getAppInstance($appId, $instanceId) {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * Get Instance.
     *
     * @param string $instanceId
     *
     * @throws GuzzleException
     *
     * @return array
     */
    public function getInstance($instanceId) {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/instances/' . $instanceId, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * Take Instance out of the service.
     *
     * @param string $appId
     * @param string $instanceId
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    public function takeInstanceOut($appId, $instanceId) {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/status', [
            'query' => [
                'value' => 'OUT_OF_SERVICE',
            ],
        ]);
    }

    /**
     * Put Instance back into the service.
     *
     * @param string $appId
     * @param string $instanceId
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    public function putInstanceBack($appId, $instanceId) {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/status', [
            'query' => [
                'value' => 'UP',
            ],
        ]);
    }

    /**
     * Update app Instance metadata.
     *
     * @param string $appId
     * @param string $instanceId
     * @param array $metadata
     *
     * @throws GuzzleException
     *
     * @return ResponseInterface
     */
    public function updateAppInstanceMetadata($appId, $instanceId, array $metadata) {
        return $this->client->request('PUT', $this->getEurekaUri() . '/apps/' . $appId . '/' . $instanceId . '/metadata', [
            'query' => $metadata,
        ]);
    }

    /**
     * Get all instances by a vip address.
     *
     * @param string $vipAddress
     *
     * @throws GuzzleException
     *
     * @return array
     */
    public function getInstancesByVipAddress($vipAddress) {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/vips/' . $vipAddress, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

    /**
     * Get all instances by a secure vip address.
     *
     * @param string $secureVipAddress
     *
     * @throws GuzzleException
     *
     * @return array
     */
    public function getInstancesBySecureVipAddress($secureVipAddress) {
        $response = $this->client->request('GET', $this->getEurekaUri() . '/svips/' . $secureVipAddress, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }

}

?>