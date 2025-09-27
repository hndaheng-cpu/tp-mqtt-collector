<?php
namespace think\mqtt;

use Workerman\Mqtt\Client;
use Workerman\Lib\Timer;

class Connector
{
    protected $client;
    protected $config;
    protected $messageCallback;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->createClient();
    }

    /**
     * 创建 MQTT 客户端
     */
    protected function createClient()
    {
        $this->client = new Client("mqtt://{$this->config['host']}:{$this->config['port']}");
    }

    /**
     * 注册消息回调
     * @param callable $callback
     */
    public function onMessage(callable $callback)
    {
        $this->messageCallback = $callback;
    }

    /**
     * 连接 MQTT Broker
     */
    public function connect()
    {
        $this->client->onConnect = function ($mqtt) {
            echo "[" . date('Y-m-d H:i:s') . "] 已连接 MQTT Broker\n";
            foreach ((array)$this->config['topics'] as $topic) {
                $mqtt->subscribe($topic);
                echo "[" . date('Y-m-d H:i:s') . "] 已订阅: $topic\n";
            }
        };

        $this->client->onMessage = function ($topic, $payload) {
            if (is_callable($this->messageCallback)) {
                call_user_func($this->messageCallback, $topic, $payload);
            }
        };

        $this->client->onError = function ($error) {
            echo "[" . date('Y-m-d H:i:s') . "] MQTT 错误: $error\n";
        };

        $this->client->onClose = function () {
            echo "[" . date('Y-m-d H:i:s') . "] MQTT 连接断开，尝试重连...\n";
            Timer::add(3, function () {
                $this->createClient();
                $this->connect();
            }, null, false);
        };

        $this->client->connect();
    }
}