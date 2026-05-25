<?php
/**
 * 宇宙测速 - 管理员密码重置工具
 * 作者: 抖音@NCYXF
 * 
 * 使用方法:
 *   浏览器访问 http://你的域名/reset_password.php
 *   输入新密码提交即可重置 admin 账号密码
 * 
 * 使用后请立即删除本文件！
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/api/db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $username = trim($_POST['username'] ?? 'admin');

    if (empty($newPassword) || empty($confirmPassword)) {
        $message = '请填写所有字段';
    } elseif (strlen($newPassword) < 6) {
        $message = '密码长度不能少于6位';
    } elseif ($newPassword !== $confirmPassword) {
        $message = '两次输入的密码不一致';
    } else {
        try {
            $db = SpeedDB::getInstance();
            $table = $db->table('admin');
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $db->getPdo()->prepare("SELECT id FROM {$table} WHERE username = ?");
            $stmt->execute([$username]);
            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $db->getPdo()->prepare("UPDATE {$table} SET password = ?, login_token = '' WHERE username = ?");
                $stmt->execute([$hash, $username]);
                $success = true;
                $message = '密码重置成功！请使用新密码登录管理后台，然后删除本文件。';
            } else {
                $stmt = $db->getPdo()->prepare("INSERT INTO {$table} (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hash]);
                $success = true;
                $message = '管理员账号已创建！请使用新密码登录管理后台，然后删除本文件。';
            }
        } catch (Exception $e) {
            $message = '操作失败：' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>宇宙测速 - 密码重置</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0e17;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e0e0e0;
        }
        .card {
            background: #131a2b;
            border: 1px solid #1e2d4a;
            border-radius: 12px;
            padding: 40px;
            width: 400px;
            max-width: 90vw;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        h1 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 8px;
            color: #00d4ff;
        }
        .subtitle {
            text-align: center;
            font-size: 13px;
            color: #8892a4;
            margin-bottom: 28px;
        }
        .field { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 13px;
            color: #a0aec0;
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 14px;
            background: #0d1220;
            border: 1px solid #1e2d4a;
            border-radius: 8px;
            color: #e0e0e0;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus { border-color: #00d4ff; }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #00d4ff, #0080ff);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover { opacity: 0.9; }
        .msg {
            text-align: center;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .msg.error { background: rgba(255,59,48,0.15); color: #ff6b6b; border: 1px solid rgba(255,59,48,0.3); }
        .msg.ok { background: rgba(0,212,255,0.1); color: #00d4ff; border: 1px solid rgba(0,212,255,0.3); }
        .warn {
            margin-top: 24px;
            padding: 12px;
            background: rgba(255,170,0,0.1);
            border: 1px solid rgba(255,170,0,0.3);
            border-radius: 8px;
            font-size: 12px;
            color: #ffaa00;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔐 密码重置</h1>
        <div class="subtitle">宇宙测速 管理员密码重置工具</div>

        <?php if ($message): ?>
            <div class="msg <?= $success ? 'ok' : 'error' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>管理员账号</label>
                <input type="text" name="username" value="admin" placeholder="默认 admin">
            </div>
            <div class="field">
                <label>新密码</label>
                <input type="password" name="password" placeholder="至少6位" required>
            </div>
            <div class="field">
                <label>确认密码</label>
                <input type="password" name="confirm_password" placeholder="再次输入新密码" required>
            </div>
            <button type="submit">重置密码</button>
        </form>

        <div class="warn">
            ⚠️ 安全提示：重置完成后请立即删除本文件（reset_password.php），避免被他人利用！
        </div>
    </div>
</body>
</html>
