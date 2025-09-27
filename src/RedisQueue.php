<?php
namespace think\mqtt;

use Predis\Client;

class RedisQueue
{
    protected $redis;
    protected $queueKey = 'mqtt:buffer';

    public function __construct(array $config)
    {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => $config['host'],
            'port'   => $config['port'],
            'password' => $config['password'],
            'database' => $config['db'],
        ]);
    }

    /**
     * 入队
     * @param array $data
     */
    public function push(array $data)
    {
        $this->redis->lpush($this->queueKey, json_encode($data));
    }

    /**
     * 出队所有消息
     * @return array
     */
    public function popAll(): array
    {
        $tmpKey = $this->queueKey . ':tmp';
        if (!$this->redis->exists($this->queueKey)) {
            return [];
        }
        $this->redis->rename($this->queueKey, $tmpKey);

        $messages = $this->redis->lrange($tmpKey, 0, -1);
        $this->redis->del($tmpKey);

        $result = [];
        foreach ($messages as $msgJson) {
            $msg = json_decode($msgJson, true);
            if ($msg) {
                $result[] = $msg;
            }
        }
        return $result;
    }

    /**
     * 获取队列长度
     * @return int
     */
    public function length(): int
    {
        return $this->redis->llen($this->queueKey);
    }
}