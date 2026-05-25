<?php
/**
 * 数据库诊断脚本
 * 访问: http://127.0.0.1/api/test_db.php
 */
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>数据库诊断</h2>";
echo "<pre>";

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ 数据库连接成功\n";
    echo "数据库: " . DB_NAME . "\n";
    echo "表前缀: " . DB_PREFIX . "\n\n";
    
    // 检查表是否存在
    $tables = ['servers', 'admin', 'history', 'settings'];
    foreach ($tables as $t) {
        $tableName = DB_PREFIX . $t;
        $stmt = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
        $exists = $stmt->fetch() ? '✅ 存在' : '❌ 不存在';
        echo "表 {$tableName}: {$exists}\n";
    }
    echo "\n";
    
    // 查询服务器数量
    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "servers");
    $count = $stmt->fetchColumn();
    echo "speed_servers 数据条数: {$count}\n\n";
    
    if ($count > 0) {
        $stmt = $pdo->query("SELECT id, name, region, status FROM " . DB_PREFIX . "servers ORDER BY id LIMIT 5");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "前5条数据:\n";
        print_r($rows);
    }
    
    // 检查admin表
    $stmt = $pdo->query("SELECT COUNT(*) FROM " . DB_PREFIX . "admin");
    $adminCount = $stmt->fetchColumn();
    echo "\nspeed_admin 数据条数: {$adminCount}\n";
    
} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
}

echo "</pre>";
