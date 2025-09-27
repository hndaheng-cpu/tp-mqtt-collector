<?php
namespace think\mqtt;

use think\Service;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'commands' => [
                'mqtt:consumer' => \think\mqtt\Command\MqttConsumer::class,
            ],
            'config' => [
                'mqtt' => include __DIR__ . '/../config/mqtt.php',
            ],
        ];
    }
}