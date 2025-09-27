<?php
namespace think\mqtt;

class Parser
{
    /**
     * 解析 MQTT 消息
     * @param string $topic
     * @param string $payload
     * @param array $topicMap
     * @param string $defaultTable
     * @return array|null
     */
    public function parse(string $topic, string $payload, array $topicMap, string $defaultTable)
    {
        $data = [
            'topic' => $topic,
            'payload' => $payload,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // 尝试解析 JSON
        $jsonData = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            // 匹配主题规则
            $tableMap = $this->matchTopic($topic, $topicMap);
            if ($tableMap) {
                $row = ['topic' => $topic, 'created_at' => date('Y-m-d H:i:s')];
                $valid = true;
                foreach ($tableMap['fields'] as $field) {
                    if (isset($jsonData[$field])) {
                        $row[$field] = $jsonData[$field];
                    } elseif (in_array($field, $tableMap['required'])) {
                        $valid = false;
                        break;
                    }
                }
                if ($valid) {
                    $row['table'] = $tableMap['table'];
                    return $row;
                }
            }
        }

        // 默认表
        $data['table'] = $defaultTable;
        return $data;
    }

    /**
     * MQTT 主题匹配（支持 + 和 # 通配符）
     * @param string $topic
     * @param array $topicMap
     * @return array|null
     */
    protected function matchTopic(string $topic, array $topicMap)
    {
        foreach ($topicMap as $pattern => $map) {
            $patternParts = explode('/', $pattern);
            $topicParts = explode('/', $topic);
            $match = true;
            for ($i = 0; $i < count($patternParts); $i++) {
                if ($patternParts[$i] == '#') break;
                if ($patternParts[$i] != '+' && (!isset($topicParts[$i]) || $patternParts[$i] != $topicParts[$i])) {
                    $match = false;
                    break;
                }
            }
            if ($match) return $map;
        }
        return null;
    }
}