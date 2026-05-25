<?php
/**
 * 宇宙测速 - 管理后台认证
 * 作者: 抖音@NCYXF
 */
require_once __DIR__ . '/../db.php';

session_start();

// 兼容 Nginx / CGI / PHP内置服务器 (getallheaders 在这些环境下不存在)
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}

function checkAdminAuth() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? $_SESSION['admin_token'] ?? '';
    $token = str_replace('Bearer ', '', $token);
    
    if (empty($token)) {
        jsonResponse(['code' => 401, 'error' => '未登录'], 401);
    }
    
    $db = SpeedDB::getInstance();
    $table = $db->table('admin');
    $stmt = $db->getPdo()->prepare("SELECT id, username FROM {$table} WHERE login_token = ? AND status = 1");
    $stmt->execute([$token]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        jsonResponse(['code' => 401, 'error' => '登录已过期'], 401);
    }
    
    return $admin;
}

// 只在直接访问 auth.php 时处理登录/退出路由
if (basename($_SERVER['SCRIPT_NAME']) === 'auth.php') {

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $data = getInput();
            $username = trim($data['username'] ?? '');
            $password = trim($data['password'] ?? '');
            
            if (empty($username) || empty($password)) {
                jsonResponse(['code' => 400, 'error' => '请输入账号和密码'], 400);
            }
            
            $db = SpeedDB::getInstance();
            $table = $db->table('admin');
            $stmt = $db->getPdo()->prepare("SELECT * FROM {$table} WHERE username = ? AND status = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if (!$admin || !password_verify($password, $admin['password'])) {
                jsonResponse(['code' => 401, 'error' => '账号或密码错误'], 401);
            }
            
            $token = bin2hex(random_bytes(32));
            $db->getPdo()->prepare("UPDATE {$table} SET login_token = ?, last_login_ip = ?, last_login_time = NOW() WHERE id = ?")
                ->execute([$token, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', $admin['id']]);
            
            $_SESSION['admin_token'] = $token;
            
            jsonResponse([
                'code' => 200,
                'data' => [
                    'token' => $token,
                    'username' => $admin['username']
                ]
            ]);
            break;
            
        case 'DELETE':
            $admin = checkAdminAuth();
            $db = SpeedDB::getInstance();
            $table = $db->table('admin');
            $db->getPdo()->prepare("UPDATE {$table} SET login_token = '' WHERE id = ?")
                ->execute([$admin['id']]);
            session_destroy();
            jsonResponse(['code' => 200, 'message' => '退出成功']);
            break;
            
        default:
            jsonResponse(['code' => 405, 'error' => '方法不允许'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['code' => 500, 'error' => $e->getMessage()], 500);
}

} // end if basename
