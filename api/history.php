<?php
/**
 * 宇宙测速 - 历史记录API
 * 作者: 抖音@NCYXF
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = SpeedDB::getInstance();
$table = $db->table('history');

try {
    switch ($method) {
        case 'GET':
            $limit = min(intval($_GET['limit'] ?? 20), 300);
            $offset = intval($_GET['offset'] ?? 0);
            $stmt = $db->getPdo()->prepare("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT ? OFFSET ?");
            $stmt->execute([$limit, $offset]);
            $rows = $stmt->fetchAll();
            jsonResponse(['code' => 200, 'data' => $rows]);
            break;

        default:
            jsonResponse(['code' => 405, 'error' => '方法不允许'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['code' => 500, 'error' => $e->getMessage()], 500);
}
