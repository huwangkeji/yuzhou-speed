<?php
/**
 * 宇宙测速 - 管理后台登录页
 * 作者: 抖音@NCYXF
 */
session_start();
require_once __DIR__ . '/../api/db.php';

// 已登录则直接跳转后台首页
if (!empty($_SESSION['admin_token'])) {
    $db = SpeedDB::getInstance();
    $table = $db->table('admin');
    $stmt = $db->getPdo()->prepare("SELECT id FROM {$table} WHERE login_token = ? AND status = 1");
    $stmt->execute([$_SESSION['admin_token']]);
    if ($stmt->fetch()) {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台登录 - 宇宙测速</title>
    <link rel="stylesheet" href="css/admin.css?v=1.0.0">
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-header">
            <h1>宇宙测速</h1>
            <p>管理后台</p>
        </div>
        <form id="login-form" class="login-form">
            <div class="form-group">
                <label>管理员账号</label>
                <input type="text" id="username" placeholder="请输入账号" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" id="password" placeholder="请输入密码" required>
            </div>
            <div class="form-error" id="login-error"></div>
            <button type="submit" class="btn-login">登 录</button>
        </form>
        <div class="login-footer">
            <p>作者: 抖音@NCYXF</p>
        </div>
    </div>
    <script>
        const API_BASE = '../api/admin/';
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorEl = document.getElementById('login-error');
            errorEl.textContent = '';

            try {
                const res = await fetch(API_BASE + 'auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                const data = await res.json();
                if (data.code === 200) {
                    localStorage.setItem('admin_token', data.data.token);
                    window.location.href = 'index.php';
                } else {
                    errorEl.textContent = data.error || '登录失败';
                }
            } catch (e) {
                errorEl.textContent = '网络错误';
            }
        });
    </script>
</body>
</html>
