<?php
/**
 * 宇宙测速 - 数据库配置
 * 作者: 抖音@NCYXF
 * 
 * 请手动修改以下数据库连接信息
 */

define('DB_HOST', 'localhost');      // 数据库地址
define('DB_PORT', '3306');           // 数据库端口
define('DB_NAME', 'yuzhou_speed');   // 数据库名称
define('DB_USER', 'root');           // 数据库用户名
define('DB_PASS', 'heicat');               // 数据库密码
define('DB_PREFIX', 'speed_');       // 表前缀

define('APP_VERSION', '1.0.0');
define('APP_NAME', '宇宙测速');

// 禁止直接访问此文件
if (basename($_SERVER['SCRIPT_NAME']) === 'config.php') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['code' => 403, 'error' => '禁止访问']);
    exit;
}
