<?php
/**
 * 宇宙测速 - 管理后台首页
 * 作者: 抖音@NCYXF
 */
session_start();
require_once __DIR__ . '/../api/db.php';

// ====== 登录态校验 ======
$adminUser = null;
$loggedIn = false;

if (!empty($_SESSION['admin_token'])) {
    $db = SpeedDB::getInstance();
    $adminTable = $db->table('admin');
    $stmt = $db->getPdo()->prepare("SELECT id, username FROM {$adminTable} WHERE login_token = ? AND status = 1");
    $stmt->execute([$_SESSION['admin_token']]);
    $adminUser = $stmt->fetch();
    if ($adminUser) {
        $loggedIn = true;
    }
}

if (!$loggedIn) {
    header('Location: login.php');
    exit;
}

// ====== 预加载数据 ======
$db = SpeedDB::getInstance();

// 统计数据
$serverTable = $db->table('servers');
$stmt = $db->getPdo()->query("SELECT COUNT(*) FROM {$serverTable}");
$serverCount = intval($stmt->fetchColumn());

$historyTable = $db->table('history');
$testCount = 0;
$avgSpeed = 0;
try {
    $stmt = $db->getPdo()->query("SELECT COUNT(*) FROM {$historyTable}");
    $testCount = intval($stmt->fetchColumn());
    if ($testCount > 0) {
        $stmt = $db->getPdo()->query("SELECT AVG(download_speed) FROM {$historyTable}");
        $avgSpeed = round(floatval($stmt->fetchColumn()), 1);
    }
} catch (Exception $e) {
    // history 表可能不存在
}

// 服务器列表（第一页，20条）
$stmt = $db->getPdo()->prepare("SELECT * FROM {$serverTable} ORDER BY sort_order ASC, id DESC LIMIT 20 OFFSET 0");
$stmt->execute();
$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 服务器总数（用于分页）
$stmt = $db->getPdo()->query("SELECT COUNT(*) FROM {$serverTable}");
$serverTotal = intval($stmt->fetchColumn());

// 设置数据
$settingsTable = $db->table('settings');
$stmt = $db->getPdo()->query("SELECT key_name, key_value FROM {$settingsTable}");
$settingsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settingsRows as $row) {
    $settings[$row['key_name']] = $row['key_value'];
}

// 构建预加载数据
$preloaded = [
    'stats' => [
        'serverCount' => $serverCount,
        'testCount' => $testCount,
        'avgSpeed' => $avgSpeed
    ],
    'servers' => $servers ?: [],
    'serverTotal' => $serverTotal,
    'settings' => $settings,
    'adminName' => $adminUser['username'] ?? 'admin'
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - 宇宙测速</title>
    <link rel="stylesheet" href="css/admin.css?v=1.0.0">
</head>
<body>
    <div class="admin-layout">
        <!-- 侧边栏 -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>宇宙测速</h2>
                <span>管理后台</span>
            </div>
            <nav class="sidebar-nav">
                <a href="#" class="nav-link active" data-page="dashboard" onclick="admin.switchPage('dashboard')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>仪表盘</span>
                </a>
                <a href="#" class="nav-link" data-page="servers" onclick="admin.switchPage('servers')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
                    <span>服务器管理</span>
                </a>
                <a href="#" class="nav-link" data-page="settings" onclick="admin.switchPage('settings')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>系统设置</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <span id="admin-user"><?php echo htmlspecialchars($adminUser['username'] ?? 'admin'); ?></span>
                <button onclick="admin.logout()">退出</button>
            </div>
        </aside>

        <!-- 主内容区 -->
        <main class="main-content">
            <!-- 仪表盘 -->
            <div id="page-dashboard" class="admin-page active">
                <div class="page-header-bar">
                    <h2>仪表盘</h2>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(0,212,255,0.15);color:#00d4ff;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/></svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value" id="stat-server-count"><?php echo $serverCount; ?></span>
                            <span class="stat-label">测速服务器</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(0,200,83,0.15);color:#00c853;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value" id="stat-test-count"><?php echo $testCount; ?></span>
                            <span class="stat-label">测速次数</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(123,47,247,0.15);color:#7b2ff7;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value" id="stat-avg-speed"><?php echo $avgSpeed; ?></span>
                            <span class="stat-label">平均下行(Mbps)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 服务器管理 -->
            <div id="page-servers" class="admin-page">
                <div class="page-header-bar">
                    <h2>服务器管理</h2>
                    <button class="btn-primary" onclick="admin.openServerModal()">+ 添加服务器</button>
                </div>
                <div class="table-card">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>名称</th>
                                <th>地区</th>
                                <th>城市</th>
                                <th>运营商</th>
                                <th>下行速度</th>
                                <th>上行速度</th>
                                <th>波动范围</th>
                                <th>Ping</th>
                                <th>测速时长</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="server-table-body"></tbody>
                    </table>
                    <div class="pagination" id="server-pagination"></div>
                </div>
            </div>

            <!-- 系统设置 -->
            <div id="page-settings" class="admin-page">
                <div class="page-header-bar">
                    <h2>系统设置</h2>
                </div>
                <div class="settings-card">
                    <div class="settings-list" id="settings-list"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- 服务器编辑弹窗 -->
    <div id="server-modal" class="modal">
        <div class="modal-overlay" onclick="admin.closeServerModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">添加服务器</h3>
                <button class="modal-close" onclick="admin.closeServerModal()">&times;</button>
            </div>
            <form id="server-form" class="modal-form">
                <input type="hidden" id="server-id">
                <div class="form-row">
                    <div class="form-group">
                        <label>服务器名称 *</label>
                        <input type="text" id="sv-name" required placeholder="如: 北京电信">
                    </div>
                    <div class="form-group">
                        <label>所属地区</label>
                        <input type="text" id="sv-region" placeholder="如: 北京">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>所属城市</label>
                        <input type="text" id="sv-city" placeholder="如: 北京（留空自动从名称提取）">
                    </div>
                    <div class="form-group">
                        <label>运营商</label>
                        <input type="text" id="sv-operator" placeholder="如: 电信（留空自动从名称提取）">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>下行速度 (Mbps)</label>
                        <input type="number" id="sv-download" step="0.1" value="100">
                    </div>
                    <div class="form-group">
                        <label>上行速度 (Mbps)</label>
                        <input type="number" id="sv-upload" step="0.1" value="50">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>速度波动范围 (%)</label>
                        <input type="number" id="sv-fluctuation" step="0.1" value="10">
                    </div>
                    <div class="form-group">
                        <label>Ping值 (ms)</label>
                        <input type="number" id="sv-ping" value="15">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>抖动值 (ms)</label>
                        <input type="number" id="sv-jitter" value="5">
                    </div>
                    <div class="form-group">
                        <label>丢包率 (%)</label>
                        <input type="number" id="sv-loss" step="0.1" value="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>测速时长 (秒)</label>
                        <input type="number" id="sv-duration" value="5">
                    </div>
                    <div class="form-group">
                        <label>排序</label>
                        <input type="number" id="sv-sort" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="sv-status" checked>
                        <span>启用</span>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="admin.closeServerModal()">取消</button>
                    <button type="submit" class="btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PHP预加载数据注入 -->
    <script>
    window.PRELOADED = <?php echo json_encode($preloaded, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="js/admin.js?v=1.0.1"></script>
</body>
</html>
