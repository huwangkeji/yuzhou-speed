<?php
/**
 * index.php 诊断脚本
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain; charset=utf-8');

echo "=== index.php 诊断 ===\n\n";

// 1. 检查 db.php
echo "1. 检查 api/db.php...\n";
$dbFile = __DIR__ . '/../api/db.php';
if (!file_exists($dbFile)) {
    echo "   ❌ 文件不存在: $dbFile\n";
    exit;
}
echo "   ✅ 文件存在\n";

// 2. 加载 db.php
echo "\n2. 加载 db.php...\n";
try {
    require_once $dbFile;
    echo "   ✅ 加载成功\n";
} catch (Throwable $e) {
    echo "   ❌ 加载失败: " . $e->getMessage() . "\n";
    echo "   文件: " . $e->getFile() . " 第 " . $e->getLine() . " 行\n";
    exit;
}

// 3. 检查 SpeedDB 类
echo "\n3. 检查 SpeedDB 类...\n";
if (!class_exists('SpeedDB')) {
    echo "   ❌ SpeedDB 类不存在\n";
    exit;
}
echo "   ✅ SpeedDB 类存在\n";

// 4. 连接数据库
echo "\n4. 连接数据库...\n";
try {
    $db = SpeedDB::getInstance();
    echo "   ✅ 连接成功\n";
} catch (Throwable $e) {
    echo "   ❌ 连接失败: " . $e->getMessage() . "\n";
    exit;
}

// 5. 检查表
echo "\n5. 检查表...\n";
try {
    $serversTable = $db->table('servers');
    echo "   服务器表名: $serversTable\n";
    $stmt = $db->getPdo()->query("SELECT COUNT(*) FROM {$serversTable}");
    $count = $stmt->fetchColumn();
    echo "   ✅ 服务器表正常, 数据条数: $count\n";
} catch (Throwable $e) {
    echo "   ❌ 服务器表错误: " . $e->getMessage() . "\n";
}

try {
    $settingsTable = $db->table('settings');
    echo "   设置表名: $settingsTable\n";
    $stmt = $db->getPdo()->query("SELECT COUNT(*) FROM {$settingsTable}");
    $count = $stmt->fetchColumn();
    echo "   ✅ 设置表正常, 数据条数: $count\n";
} catch (Throwable $e) {
    echo "   ❌ 设置表错误: " . $e->getMessage() . "\n";
}

// 6. 模拟 index.php 的关键逻辑
echo "\n6. 模拟 index.php 逻辑...\n";
try {
    $table = $db->table('servers');
    $stmt = $db->getPdo()->prepare("SELECT * FROM {$table} WHERE status = 1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute();
    $servers = $stmt->fetchAll();
    echo "   ✅ 查询服务器成功, 条数: " . count($servers) . "\n";

    $defaultServer = $servers[0] ?? null;
    echo "   默认服务器: " . ($defaultServer['name'] ?? '无') . "\n";

    $settings = [];
    $settingsTable = $db->table('settings');
    $stmt = $db->getPdo()->prepare("SELECT key_name, key_value FROM {$settingsTable}");
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $settings[$row['key_name']] = $row['key_value'];
    }
    echo "   ✅ 查询设置成功, 条数: " . count($settings) . "\n";

    // 测试 json_encode
    $json = json_encode($servers, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        echo "   ❌ json_encode 失败: " . json_last_error_msg() . "\n";
    } else {
        echo "   ✅ json_encode 成功\n";
    }

} catch (Throwable $e) {
    echo "   ❌ 模拟失败: " . $e->getMessage() . "\n";
    echo "   文件: " . $e->getFile() . " 第 " . $e->getLine() . " 行\n";
}

echo "\n=== 诊断完成 ===\n";
