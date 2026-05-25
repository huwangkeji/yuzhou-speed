<?php
/**
 * 宇宙测速 - 主入口
 * 作者: 抖音@NCYXF
 */
require_once __DIR__ . '/api/db.php';

// 从数据库读取服务器列表
$db = SpeedDB::getInstance();
$table = $db->table('servers');
try {
    $stmt = $db->getPdo()->prepare("SELECT * FROM {$table} WHERE status = 1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute();
    $servers = $stmt->fetchAll();
} catch (Exception $e) {
    $servers = [];
}

$defaultServer = $servers[0] ?? null;

// 获取客户端IP
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$clientIp = explode(',', $clientIp)[0];
$clientIp = trim(filter_var($clientIp, FILTER_VALIDATE_IP) ?: '127.0.0.1');

// 从数据库读取所有设置
$settings = [];
try {
    $settingsTable = $db->table('settings');
    $stmt = $db->getPdo()->prepare("SELECT key_name, key_value FROM {$settingsTable}");
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        $settings[$row['key_name']] = $row['key_value'];
    }
} catch (Exception $e) {
    $settings = [];
}

// 从设置中读取 IP API URL
$ipApiUrl = $settings['ip_api_url'] ?? '';

// 查询 IP 运营商信息
$operator = '未知运营商';
if ($ipApiUrl && $clientIp !== '127.0.0.1') {
    // 使用配置的 API 查询
    $apiUrl = str_replace('{ip}', urlencode($clientIp), $ipApiUrl);
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $ipData = json_decode($response, true);
        // 适配常见 API 返回格式：优先取 isp/operator/carrier
        $operator = $ipData['data']['isp'] ?? $ipData['data']['operator'] ?? $ipData['isp'] ?? $ipData['operator'] ?? $ipData['carrier'] ?? '未知运营商';
    }
}

// API 不可用时用简易号段推断兜底
if ($operator === '未知运营商') {
    if (preg_match('/^223\.1|^111\.|^117\./', $clientIp)) {
        $operator = '中国移动';
    } elseif (preg_match('/^110\.|^60\.|^61\./', $clientIp)) {
        $operator = '中国联通';
    } elseif (preg_match('/^180\.|^101\.|^106\./', $clientIp)) {
        $operator = '中国电信';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>宇宙测速</title>
<link rel="stylesheet" href="app/css/style.css">
<script>
// PHP注入数据库服务器列表，替代静态 servers.js
window.SERVER_DATA = <?php echo json_encode($servers, JSON_UNESCAPED_UNICODE); ?>;
window.DEFAULT_SERVER = <?php echo json_encode($defaultServer, JSON_UNESCAPED_UNICODE); ?>;
window.SERVER_SETTINGS = <?php echo json_encode($settings, JSON_UNESCAPED_UNICODE); ?>;
</script>
</head>
<body>

<!-- ================================================== -->
<!--                  页面：网络测速                       -->
<!-- ================================================== -->
<div id="page-speed" class="page speed-page active">
  <div class="speed-main">
    <div class="page-header">宇宙测速</div>

    <!-- 指标栏 -->
    <div class="metrics-bar">
      <div class="metric-item">
        <span class="metric-label">Ping时延</span>
        <span class="metric-value" id="ping-value"><span class="m-num">--</span><span class="m-unit"></span></span>
      </div>
      <div class="metric-item">
        <span class="metric-label">下行速率</span>
        <span class="metric-value" id="dl-metric"><span class="m-num">--</span><span class="m-unit"></span></span>
      </div>
      <div class="metric-item">
        <span class="metric-label">上行速率</span>
        <span class="metric-value" id="ul-metric"><span class="m-num">--</span><span class="m-unit"></span></span>
      </div>
    </div>

    <!-- 提示条 -->
    <div class="tip-bar">未开启定位权限，可能无法享受完整测速功能及其他工具哦~</div>

    <!-- 仪表盘 -->
    <div class="gauge-area">
      <div class="gauge-wrap">
        <canvas id="speed-gauge" width="300" height="260"></canvas>
        <div class="gauge-center">
          <span class="gauge-speed" id="current-speed">0.00</span>
          <span class="gauge-unit" id="speed-unit">Mbps</span>
        </div>
      </div>
    </div>

    <!-- 开始按钮 -->
    <div class="action-area">
      <button id="btn-go" class="btn-go">开始测试</button>
    </div>

    <!-- 波形图 -->
    <div class="charts-area">
      <div class="chart-box">
        <div class="chart-title"><span class="dot dl"></span>下行速率</div>
        <canvas id="dl-wave" width="200" height="60"></canvas>
      </div>
      <div class="chart-box">
        <div class="chart-title"><span class="dot ul"></span>上行速率</div>
        <canvas id="ul-wave" width="200" height="60"></canvas>
      </div>
    </div>
  </div>

  <!-- 底部信息栏 -->
  <div class="info-bar">
    <div class="info-left">
      <div class="info-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#8a92a8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
      </div>
      <div class="info-txt">
        <span class="info-main" id="user-ip"><?php echo htmlspecialchars($clientIp); ?></span>
        <span class="info-sub" id="user-op"><?php echo htmlspecialchars($operator); ?></span>
      </div>
    </div>
    <div class="info-right" onclick="app.showServerPage()">
      <div class="info-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#8a92a8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
      </div>
      <div class="info-txt">
        <span class="info-label">测速服务器</span>
        <span class="info-main" id="srv-name"><?php echo htmlspecialchars($defaultServer['name'] ?? '石家庄移动'); ?> <span class="info-arrow">&#9664;</span></span>
      </div>
    </div>
  </div>
</div>

<!-- ================================================== -->
<!--                  页面：体验测速                       -->
<!-- ================================================== -->
<div id="page-exp" class="page sub-page" style="display:none">
  <div class="page-header">体验测速</div>
  <div class="exp-grid">
    <div class="exp-card">
      <div class="exp-icon exp-icon-web"></div>
      <span class="exp-label">网站</span>
    </div>
    <div class="exp-card">
      <div class="exp-icon exp-icon-video"></div>
      <span class="exp-label">视频</span>
    </div>
    <div class="exp-card">
      <div class="exp-icon exp-icon-file"></div>
      <span class="exp-label">文件</span>
    </div>
    <div class="exp-card">
      <div class="exp-icon exp-icon-game"></div>
      <span class="exp-label">游戏</span>
    </div>
  </div>
</div>

<!-- ================================================== -->
<!--                  页面：自动测速                       -->
<!-- ================================================== -->
<div id="page-auto" class="page sub-page" style="display:none">
  <div class="page-header">自动测速</div>
  <div class="auto-page">
    <!-- 动态圆环开始按钮 -->
    <div class="auto-start-wrap">
      <div class="auto-ring auto-ring-outer"></div>
      <div class="auto-ring auto-ring-inner"></div>
      <div class="auto-start-btn">开始</div>
    </div>
    <!-- 设置列表 -->
    <div class="auto-settings">
      <div class="auto-setting-item">
        <span class="auto-setting-label">测速内容</span>
        <span class="auto-setting-val">请点击进入 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
      </div>
      <div class="auto-setting-item">
        <span class="auto-setting-label">时间间隔</span>
        <span class="auto-setting-val">3秒 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
      </div>
      <div class="auto-setting-item">
        <span class="auto-setting-label">测试轮次</span>
        <span class="auto-setting-val">3次 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
      </div>
      <div class="auto-setting-item">
        <span class="auto-setting-label">后台状态</span>
        <span class="auto-setting-val"></span>
      </div>
      <div class="auto-setting-item">
        <span class="auto-setting-label">测速记录</span>
        <span class="auto-setting-val">保留300条 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
      </div>
    </div>
    <!-- 提示 -->
    <div class="auto-tip">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="#FF9F43" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      <span>自动测速将消耗较多流量，请谨慎选择测试内容</span>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="#FF9F43" stroke="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
    </div>
  </div>
</div>

<!-- ================================================== -->
<!--                  页面：网络诊断                       -->
<!-- ================================================== -->
<div id="page-diag" class="page sub-page" style="display:none">
  <div class="page-header">网络诊断</div>
  <div class="diag-page">
    <!-- 标签切换 -->
    <div class="diag-tabs">
      <div class="diag-tab active">工具</div>
      <div class="diag-tab">WIFI</div>
    </div>
    <!-- Ping测试 -->
    <div class="diag-section expanded">
      <div class="diag-section-title">
        <span>Ping测试</span>
        <svg class="diag-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
      <div class="diag-section-body">
        <div class="diag-checks">
          <label class="diag-check"><input type="checkbox"><span>中国香港</span></label>
          <label class="diag-check"><input type="checkbox"><span>中国台湾</span></label>
          <label class="diag-check"><input type="checkbox"><span>中国澳门</span></label>
          <label class="diag-check"><input type="checkbox"><span>亚洲</span></label>
        </div>
        <input type="text" class="diag-input" placeholder="输入发送包个数,默认5个">
      </div>
    </div>
    <!-- DNS测试 -->
    <div class="diag-section">
      <div class="diag-section-title">
        <span>DNS测试</span>
        <svg class="diag-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
      </div>
      <div class="diag-section-body"></div>
    </div>
    <!-- DIG测试 -->
    <div class="diag-section">
      <div class="diag-section-title">
        <span>DIG测试</span>
        <svg class="diag-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
      </div>
      <div class="diag-section-body"></div>
    </div>
    <!-- 底部按钮 -->
    <div class="diag-action">
      <button class="btn-diag">PING测试</button>
    </div>
  </div>
</div>

<!-- ================================================== -->
<!--                  页面：设置                          -->
<!-- ================================================== -->
<div id="page-set" class="page sub-page" style="display:none">
  <div class="page-header">设置</div>
  <div class="set-page">
    <!-- Logo区域 -->
    <div class="set-logo-area">
      <div class="set-gauge">
        <svg viewBox="0 0 200 120" width="160" height="96">
          <defs>
            <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" style="stop-color:#4A9DFF"/>
              <stop offset="50%" style="stop-color:#38F9D7"/>
              <stop offset="100%" style="stop-color:#43E97B"/>
            </linearGradient>
          </defs>
          <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="url(#gaugeGrad)" stroke-width="12" stroke-linecap="round"/>
          <path d="M100 100 L100 45" stroke="#8a92a8" stroke-width="3" stroke-linecap="round" transform="rotate(45 100 100)"/>
          <circle cx="100" cy="100" r="6" fill="#8a92a8"/>
        </svg>
      </div>
      <div class="set-brand">宇宙测速</div>
    </div>
    <!-- 设置列表 -->
    <div class="set-list">
      <div class="set-item">
        <span class="set-label">测试记录</span>
        <span class="set-val">保留300条 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
      </div>
      <div class="set-item">
        <span class="set-label">服务器配置</span>
        <svg class="set-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <div class="set-item">
        <span class="set-label">流量设置</span>
        <span class="set-val">4963.7/5120.0M <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
      </div>
      <div class="set-item">
        <span class="set-label">测速设置</span>
        <svg class="set-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <div class="set-item">
        <span class="set-label">场所设置</span>
        <svg class="set-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <div class="set-item">
        <span class="set-label">意见反馈</span>
        <svg class="set-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <div class="set-item">
        <span class="set-label">版本信息</span>
        <svg class="set-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <div class="set-item">
        <span class="set-label">系统信息</span>
        <svg class="set-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <div class="set-item">
        <span class="set-label">退出程序</span>
        <svg class="set-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
    </div>
  </div>
</div>

<!-- ================================================== -->
<!--                  页面：服务器列表                     -->
<!-- ================================================== -->
<div id="page-server" class="page sub-page" style="display:none">
  <div class="server-page-header">
    <button class="back-btn" onclick="app.backFromServer()">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="page-title">测速服务器</div>
  </div>
  <div class="server-list" id="server-list"></div>
</div>

<!-- ================================================== -->
<!--                  底部导航栏                          -->
<!-- ================================================== -->
<nav class="bottom-nav" id="bottom-nav">
  <div class="nav-item active" data-page="speed" onclick="app.switchTab('speed')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
    <span class="nav-label">网络测速</span>
  </div>
  <div class="nav-item" data-page="exp" onclick="app.switchTab('exp')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    <span class="nav-label">体验测速</span>
  </div>
  <div class="nav-item" data-page="auto" onclick="app.switchTab('auto')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/></svg>
    <span class="nav-label">自动测速</span>
  </div>
  <div class="nav-item" data-page="diag" onclick="app.switchTab('diag')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
    <span class="nav-label">网络诊断</span>
  </div>
  <div class="nav-item" data-page="set" onclick="app.switchTab('set')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.68 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
    <span class="nav-label">设置</span>
  </div>
</nav>

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- JS 模块 (按依赖顺序加载，servers.js 改为从数据库动态加载) -->
<script src="app/js/servers.js"></script>
<script src="app/js/gauge.js"></script>
<script src="app/js/wave.js"></script>
<script src="app/js/speed-test.js"></script>
<script src="app/js/app.js"></script>

</body>
</html>
