<?php
/**
 * 宇宙测速 - 管理后台服务器管理API
 * 作者: 抖音@NCYXF
 */
require_once __DIR__ . '/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = SpeedDB::getInstance();
$table = $db->table('servers');

try {
    switch ($method) {
        case 'GET':
            checkAdminAuth();
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $stmt = $db->getPdo()->prepare("SELECT * FROM {$table} WHERE id = ?");
                $stmt->execute([$id]);
                $server = $stmt->fetch();
                jsonResponse(['code' => 200, 'data' => $server]);
            } else {
                $page = max(1, intval($_GET['page'] ?? 1));
                $limit = min(50, intval($_GET['limit'] ?? 20));
                $offset = ($page - 1) * $limit;
                $keyword = $_GET['keyword'] ?? '';
                
                $where = 'WHERE 1=1';
                $params = [];
                if ($keyword) {
                    $where .= " AND (name LIKE ? OR region LIKE ?)";
                    $params[] = "%{$keyword}%";
                    $params[] = "%{$keyword}%";
                }
                
                $stmt = $db->getPdo()->prepare("SELECT COUNT(*) FROM {$table} {$where}");
                $stmt->execute($params);
                $total = $stmt->fetchColumn();
                
                $sql = "SELECT * FROM {$table} {$where} ORDER BY sort_order ASC, id DESC LIMIT ? OFFSET ?";
                $stmt = $db->getPdo()->prepare($sql);
                $stmt->execute(array_merge($params, [$limit, $offset]));
                $servers = $stmt->fetchAll();
                
                jsonResponse([
                    'code' => 200,
                    'data' => $servers,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => intval($total),
                        'pages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;

        case 'POST':
            checkAdminAuth();
            $data = getInput();
            $fields = ['name', 'region', 'city', 'operator', 'download_speed', 'upload_speed', 'speed_fluctuation',
                       'ping_value', 'jitter_value', 'packet_loss', 'test_duration', 'status', 'sort_order'];

            $insertData = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $insertData[$field] = is_numeric($data[$field]) ? floatval($data[$field]) : trim($data[$field]);
                }
            }

            if (empty($insertData['name'])) {
                jsonResponse(['code' => 400, 'error' => '服务器名称不能为空'], 400);
            }

            // city 未填写时从 name 自动提取（如"北京电信"→"北京"）
            if (empty($insertData['city']) && !empty($insertData['name'])) {
                preg_match('/^(.*?)(?:电信|联通|移动|广电|长城|教育网|铁通)/u', $insertData['name'], $m);
                $insertData['city'] = $m[1] ?? '';
            }
            // operator 未填写时从 name 自动提取
            if (empty($insertData['operator']) && !empty($insertData['name'])) {
                preg_match('/(电信|联通|移动|广电|长城|教育网|铁通)/u', $insertData['name'], $m);
                $insertData['operator'] = $m[1] ?? '';
            }

            $columns = implode(',', array_keys($insertData));
            $placeholders = implode(',', array_fill(0, count($insertData), '?'));
            $stmt = $db->getPdo()->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
            $stmt->execute(array_values($insertData));

            jsonResponse(['code' => 200, 'message' => '添加成功', 'id' => $db->getPdo()->lastInsertId()]);
            break;

        case 'PUT':
            checkAdminAuth();
            $data = getInput();
            $id = intval($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(['code' => 400, 'error' => 'ID不能为空'], 400);
            }
            
            $fields = ['name', 'region', 'city', 'operator', 'download_speed', 'upload_speed', 'speed_fluctuation',
                       'ping_value', 'jitter_value', 'packet_loss', 'test_duration', 'status', 'sort_order'];

            $updateData = [];
            $updateFields = [];
            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "{$field} = ?";
                    $updateData[] = is_numeric($data[$field]) ? floatval($data[$field]) : trim($data[$field]);
                }
            }
            
            if (empty($updateFields)) {
                jsonResponse(['code' => 400, 'error' => '没有要更新的字段'], 400);
            }
            
            $updateData[] = $id;
            $sql = "UPDATE {$table} SET " . implode(',', $updateFields) . " WHERE id = ?";
            $stmt = $db->getPdo()->prepare($sql);
            $stmt->execute($updateData);
            
            jsonResponse(['code' => 200, 'message' => '更新成功']);
            break;

        case 'DELETE':
            checkAdminAuth();
            $id = intval($_GET['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(['code' => 400, 'error' => 'ID不能为空'], 400);
            }
            
            $stmt = $db->getPdo()->prepare("DELETE FROM {$table} WHERE id = ?");
            $stmt->execute([$id]);
            
            jsonResponse(['code' => 200, 'message' => '删除成功']);
            break;

        default:
            jsonResponse(['code' => 405, 'error' => '方法不允许'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['code' => 500, 'error' => $e->getMessage()], 500);
}
