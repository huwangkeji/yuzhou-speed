<?php
/**
 * 宇宙测速 - 服务器API
 * 作者: 抖音@NCYXF
 */
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = SpeedDB::getInstance();
$table = $db->table('servers');

try {
    switch ($method) {
        case 'GET':
            $region = $_GET['region'] ?? '';
            $sql = "SELECT * FROM {$table} WHERE status = 1";
            $params = [];
            if ($region) {
                $sql .= " AND region = ?";
                $params[] = $region;
            }
            $sql .= " ORDER BY sort_order ASC, id ASC";
            $stmt = $db->getPdo()->prepare($sql);
            $stmt->execute($params);
            $servers = $stmt->fetchAll();

            $regions = $db->getPdo()->query("SELECT DISTINCT region FROM {$table} WHERE status = 1 ORDER BY region")
                ->fetchAll(PDO::FETCH_COLUMN);

            jsonResponse([
                'code' => 200,
                'data' => $servers,
                'regions' => $regions
            ]);
            break;

        case 'POST':
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->getPdo()->prepare("SELECT * FROM {$table} WHERE id = ? AND status = 1");
                $stmt->execute([$id]);
                $server = $stmt->fetch();
                if (!$server) {
                    jsonResponse(['code' => 404, 'error' => '服务器不存在'], 404);
                }
                jsonResponse(['code' => 200, 'data' => $server]);
            }
            jsonResponse(['code' => 400, 'error' => '参数错误'], 400);
            break;

        default:
            jsonResponse(['code' => 405, 'error' => '方法不允许'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['code' => 500, 'error' => $e->getMessage()], 500);
}
