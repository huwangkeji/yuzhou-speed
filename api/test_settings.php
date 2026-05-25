<?php
require_once __DIR__ . '/db.php';
header('Content-Type: text/html; charset=utf-8');

echo "<h2>Settings 表诊断</h2><pre>";

try {
    $db = SpeedDB::getInstance();
    $table = $db->table('settings');
    
    // 确保 dl_ul_interval 存在
    $check = $db->getPdo()->prepare("SELECT id FROM {$table} WHERE key_name = 'dl_ul_interval'");
    $check->execute();
    if (!$check->fetch()) {
        $db->getPdo()->prepare("INSERT INTO {$table} (key_name, key_value) VALUES ('dl_ul_interval', '3')")->execute();
        echo "已插入默认配置: dl_ul_interval = 3\n\n";
    }
    
    // 查看表结构
    $stmt = $db->getPdo()->query("DESCRIBE {$table}");
    echo "表结构:\n";
    while ($row = $stmt->fetch()) {
        echo "  {$row['Field']} {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']}\n";
    }
    echo "\n";
    
    // 查看现有数据
    $stmt = $db->getPdo()->query("SELECT * FROM {$table}");
    echo "现有数据:\n";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage();
}

echo "</pre>";
