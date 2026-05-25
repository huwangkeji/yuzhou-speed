<?php
/**
 * 宇宙测速 - 设置API
 * 作者: 抖音@NCYXF
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = SpeedDB::getInstance();
$table = $db->table('settings');

try {
    switch ($method) {
        case 'GET':
            $key = $_GET['key'] ?? '';
            if ($key) {
                $stmt = $db->getPdo()->prepare("SELECT key_value FROM {$table} WHERE key_name = ?");
                $stmt->execute([$key]);
                $row = $stmt->fetch();
                jsonResponse(['code' => 200, 'data' => $row ? $row['key_value'] : null]);
            } else {
                $stmt = $db->getPdo()->query("SELECT key_name, key_value FROM {$table}");
                $settings = [];
                while ($row = $stmt->fetch()) {
                    $settings[$row['key_name']] = $row['key_value'];
                }
                jsonResponse(['code' => 200, 'data' => $settings]);
            }
            break;

        case 'PUT':
            $data = getInput();
            if (empty($data) || !is_array($data)) {
                jsonResponse(['code' => 400, 'error' => '请提供要更新的配置'], 400);
            }
            foreach ($data as $key => $value) {
                // 先检查 key 是否存在
                $check = $db->getPdo()->prepare("SELECT id FROM {$table} WHERE key_name = ?");
                $check->execute([$key]);
                if ($check->fetch()) {
                    $stmt = $db->getPdo()->prepare("UPDATE {$table} SET key_value = ?, updated_at = NOW() WHERE key_name = ?");
                    $stmt->execute([$value, $key]);
                } else {
                    $stmt = $db->getPdo()->prepare("INSERT INTO {$table} (key_name, key_value) VALUES (?, ?)");
                    $stmt->execute([$key, $value]);
                }
            }
            jsonResponse(['code' => 200, 'message' => '保存成功']);
            break;

        default:
            jsonResponse(['code' => 405, 'error' => '方法不允许'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['code' => 500, 'error' => $e->getMessage()], 500);
}
