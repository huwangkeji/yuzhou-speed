<?php
/**
 * 宇宙测速 - 仿真测速API
 * 作者: 抖音@NCYXF
 * 
 * 返回仿真测速数据，基于服务器配置生成波动速度
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    jsonResponse(['code' => 405, 'error' => '仅支持POST请求'], 405);
}

$data = getInput();
$serverId = intval($data['server_id'] ?? 0);
$testType = $data['type'] ?? 'download'; // download, upload, ping

if ($serverId <= 0) {
    jsonResponse(['code' => 400, 'error' => '请选择测速服务器'], 400);
}

try {
    $db = SpeedDB::getInstance();
    $table = $db->table('servers');
    $stmt = $db->getPdo()->prepare("SELECT * FROM {$table} WHERE id = ? AND status = 1");
    $stmt->execute([$serverId]);
    $server = $stmt->fetch();

    if (!$server) {
        jsonResponse(['code' => 404, 'error' => '服务器不存在或已禁用'], 404);
    }

    $result = [];

    switch ($testType) {
        case 'ping':
            $result = [
                'ping' => $server['ping_value'] + rand(-2, 2),
                'jitter' => $server['jitter_value'] + rand(-1, 1),
                'packet_loss' => max(0, $server['packet_loss'] + (rand(-10, 10) / 10)),
            ];
            break;

        case 'download':
        case 'upload':
            $baseSpeed = $testType === 'download' ? $server['download_speed'] : $server['upload_speed'];
            $fluctuation = $server['speed_fluctuation'] / 100;
            $duration = $testType === 'download' 
                ? intval($server['test_duration'] ?? 5) 
                : intval($server['test_duration'] ?? 5);

            $speedPoints = [];
            $pointCount = $duration * 5; // 每秒5个数据点
            
            for ($i = 0; $i < $pointCount; $i++) {
                $progress = $i / $pointCount;
                // 模拟启动爬坡和稳定波动
                if ($progress < 0.2) {
                    $factor = $progress / 0.2 * 0.7 + 0.1;
                } elseif ($progress < 0.8) {
                    $factor = 0.8 + sin($progress * 10) * 0.1;
                } else {
                    $factor = 0.85 + sin($progress * 15) * 0.08;
                }
                
                $randomFactor = 1 + (rand(-100, 100) / 100) * $fluctuation;
                $speed = $baseSpeed * $factor * $randomFactor;
                $speed = max(0.1, $speed);
                $speedPoints[] = round($speed, 2);
            }

            $avgSpeed = array_sum($speedPoints) / count($speedPoints);
            $maxSpeed = max($speedPoints);

            $result = [
                'speed_points' => $speedPoints,
                'average_speed' => round($avgSpeed, 2),
                'max_speed' => round($maxSpeed, 2),
                'duration' => $duration,
                'type' => $testType,
            ];
            break;

        default:
            jsonResponse(['code' => 400, 'error' => '不支持的测速类型'], 400);
    }

    // 保存历史记录
    if ($testType === 'download' || $testType === 'upload') {
        $historyTable = $db->table('history');
        $db->getPdo()->prepare("INSERT INTO {$historyTable} 
            (server_id, server_name, download_speed, upload_speed, test_time, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([
                $serverId,
                $server['name'],
                $testType === 'download' ? $result['average_speed'] : 0,
                $testType === 'upload' ? $result['average_speed'] : 0,
                $result['duration'],
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);
    }

    jsonResponse([
        'code' => 200,
        'data' => $result,
        'server' => [
            'id' => $server['id'],
            'name' => $server['name'],
            'region' => $server['region'],
        ]
    ]);

} catch (Exception $e) {
    jsonResponse(['code' => 500, 'error' => $e->getMessage()], 500);
}
