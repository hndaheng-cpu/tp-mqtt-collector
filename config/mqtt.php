<?php
return [
    'mqtt' => [
        'host'      => '127.0.0.1',
        'port'      => 1883,
        'username'  => '',
        'password'  => '',
        'client_id' => 'tp_mqtt_' . uniqid(),
        'topics'    => ['sensor/#', 'device/#'],
    ],
    'redis' => [
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'password' => '',
        'db'       => 0,
    ],
    'buffer_size'    => 100,
    'buffer_timeout' => 5,
    'index_suggestion' => [
        'enabled'         => true,
        'min_selectivity' => 0.1,
        'check_frequency' => 1000,
    ],
    'topic_map' => [
        'sensor/+/temp' => [
            'table'    => 'sensor_temp',
            'fields'   => ['device_id', 'temperature', 'humidity'],
            'required' => ['device_id', 'temperature']
        ],
        'device/+/status' => [
            'table'    => 'device_status',
            'fields'   => ['device_id', 'status', 'battery'],
            'required' => ['device_id', 'status']
        ]
    ],
    'default_table' => 'mqtt_messages',
];