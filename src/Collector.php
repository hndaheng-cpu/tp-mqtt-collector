<?php
namespace think\mqtt;

use Workerman\Worker;
use think\facade\Config;

class Collector
{
    protected $connector;
    protected $redisQueue;
    protected $dbHandler;
    protected $parser;
    protected $config;

    public function __construct()
    {
        $this->config = Config::get('mqtt');
        $this->connector = new Connector($this->config['mqtt']);
        $this->redisQueue = new RedisQueue($this->config['redis']);
        $this->dbHandler = new DbHandler();
        $this->parser = new Parser();
    }

    public function start()
    {
        $this->connector->onMessage(function ($topic, $payload) {
            $data = $this->parser->parse($topic, $payload, $this->config['topic_map'], $this->config['default_table']);
            if ($data) {
                $this->redisQueue->push($data);
                if ($this->redisQueue->length() >= $this->config['buffer_size']) {
                    $this->flush();
                }
            }
        });

        // 定时写入
        \Workerman\Lib\Timer::add($this->config['buffer_timeout'], function () {
            $this->flush();
        });

        $this->connector->connect();
        Worker::runAll();
    }

    protected function flush()
    {
        $messages = $this->redisQueue->popAll();
        if (!empty($messages)) {
            $this->dbHandler->save($messages, $this->config);
        }
    }
}