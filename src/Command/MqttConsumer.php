<?php
namespace think\mqtt\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\mqtt\Collector;

class MqttConsumer extends Command
{
    protected function configure()
    {
        $this->setName('mqtt:consumer')
             ->setDescription('Start MQTT data collector');
    }

    protected function execute(Input $input, Output $output)
    {
        $collector = new Collector();
        $collector->start();
    }
}