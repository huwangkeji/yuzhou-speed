/**
 * 主应用模块
 * 作者: 抖音@NCYXF
 */

const app = {
  testing: false,
  gauge: null,
  dlWave: null,
  ulWave: null,
  tester: null,
  server: null,
  curPage: 'speed',
  $: {},

  // ==================== 初始化 ====================
  init() {
    this.cacheDOM();
    // 优先使用PHP注入的默认服务器，否则取列表第一个
    this.server = (window.DEFAULT_SERVER && window.DEFAULT_SERVER.id) ? ServerData.getById(window.DEFAULT_SERVER.id) : ServerData.getAll()[0];
    this.gauge = new SpeedGauge(this.$['speed-gauge']);
    this.gauge.draw(0);
    this.dlWave = new WaveChart(this.$['dl-wave'], '#4A9DFF');
    this.ulWave = new WaveChart(this.$['ul-wave'], '#4A9DFF');
    this.renderServerList();
    this.bindEvents();
  },

  cacheDOM() {
    const ids = [
      'speed-gauge', 'current-speed', 'speed-unit', 'btn-go',
      'ping-value', 'dl-metric', 'ul-metric',
      'dl-wave', 'ul-wave',
      'result-area', 'res-dl', 'res-ul', 'res-ping', 'res-jitter', 'res-loss',
      'srv-name', 'server-list',
      'page-speed', 'page-exp', 'page-auto', 'page-diag', 'page-set',
      'page-server',
      'toast'
    ];
    ids.forEach(id => {
      const el = document.getElementById(id);
      if (el) this.$[id] = el;
    });
  },

  bindEvents() {
    // 开始按钮
    this.$['btn-go'].addEventListener('click', () => {
      if (this.testing) {
        this.stopTest();
      } else {
        this.startTest();
      }
    });
  },

  // ==================== 测速 ====================
  startTest() {
    if (this.testing || !this.server) return;
    this.testing = true;

    const btn = this.$['btn-go'];
    const speedEl = this.$['current-speed'];
    const unitEl = this.$['speed-unit'];
    const pingEl = this.$['ping-value'];
    const dlM = this.$['dl-metric'];
    const ulM = this.$['ul-metric'];
    const resArea = this.$['result-area'];

    // 辅助: 更新指标栏数字+单位
    const setMetric = (el, num, unit) => {
      const n = el.querySelector('.m-num');
      const u = el.querySelector('.m-unit');
      if (n) n.textContent = num;
      if (u) u.textContent = unit;
    };

    // UI 切换到测试状态
    btn.classList.add('testing');
    btn.textContent = '停止测试';
    resArea.classList.remove('show');
    this.dlWave.clear();
    this.ulWave.clear();
    speedEl.classList.add('testing');
    setMetric(dlM, '--', '');
    dlM.className = 'metric-value';
    setMetric(ulM, '--', '');
    ulM.className = 'metric-value';

    // 读取间隔配置（从PHP注入或默认3秒）
    const dlUlInterval = (window.SERVER_SETTINGS && window.SERVER_SETTINGS.dl_ul_interval)
      ? parseInt(window.SERVER_SETTINGS.dl_ul_interval) : 3;

    // 创建测速器
    this.tester = new SpeedTester({
      dlUlInterval: dlUlInterval,
      onPingUpdate: (val) => {
        unitEl.textContent = 'ms';
        setMetric(pingEl, val, 'ms');
      },
      onSpeedUpdate: (type, speed) => {
        const metricEl = type === 'download' ? dlM : ulM;
        const wave = type === 'download' ? this.dlWave : this.ulWave;

        // 下载和上传阶段都显示 Mbps
        unitEl.textContent = 'Mbps';

        speedEl.textContent = speed.toFixed(2);
        this.gauge.draw(speed);
        wave.add(speed);
        if (metricEl) setMetric(metricEl, speed.toFixed(2), 'Mbps');
      },
      onPhaseChange: (phase) => {
        const statusMap = {
          ping: '正在测试Ping...',
          download: '正在测试下载速度...',
          interval: '准备测试上传速度...',
          upload: '正在测试上传速度...',
          complete: '测速完成'
        };
        // 可扩展状态显示
      },
      onInterval: (seconds) => {
        // 间隔倒计时：在速度区域显示倒计时
        speedEl.textContent = seconds;
        speedEl.classList.remove('testing');
        unitEl.textContent = 's';
      },
      onComplete: (result) => {
        // 测速完成后仪表盘不显示数据，清空为初始状态
        speedEl.textContent = '0.00';
        speedEl.classList.remove('testing');
        unitEl.textContent = 'Mbps';

        // 始终隐藏结果详情区域，只弹出toast提示
        resArea.classList.remove('show');
        this.showToast('测速完成');
        this.gauge.draw(0);
        this.resetTest();
      }
    });

    this.tester.run(this.server).catch(() => this.resetTest());
  },

  stopTest() {
    if (this.tester) this.tester.abort();
    this.resetTest();
  },

  resetTest() {
    this.testing = false;
    this.tester = null;
    const btn = this.$['btn-go'];
    btn.classList.remove('testing');
    btn.textContent = '开始测试';
    this.$['current-speed'].classList.remove('testing');
  },

  // ==================== 导航 ====================
  switchTab(page) {
    this.curPage = page;

    // 更新导航高亮
    document.querySelectorAll('.nav-item').forEach(el => {
      el.classList.toggle('active', el.dataset.page === page);
    });

    // 切换页面
    const pageMap = {
      speed: 'page-speed',
      exp: 'page-exp',
      auto: 'page-auto',
      diag: 'page-diag',
      set: 'page-set'
    };

    // 隐藏所有主页面
    Object.values(pageMap).forEach(pid => {
      const el = document.getElementById(pid);
      if (el) el.style.display = 'none';
    });
    // 隐藏服务器列表页
    const srvPage = document.getElementById('page-server');
    if (srvPage) srvPage.style.display = 'none';

    // 显示目标页面
    const targetId = pageMap[page];
    if (targetId) {
      const target = document.getElementById(targetId);
      if (target) target.style.display = '';
    }

    // 非测速页面提示
    if (page !== 'speed') {
      this.showToast('功能开发中');
    }
  },

  showServerPage() {
    // 隐藏当前页面
    const pageMap = { speed: 'page-speed', exp: 'page-exp', auto: 'page-auto', diag: 'page-diag', set: 'page-set' };
    Object.values(pageMap).forEach(pid => {
      const el = document.getElementById(pid);
      if (el) el.style.display = 'none';
    });

    // 显示服务器页面
    const srvPage = document.getElementById('page-server');
    if (srvPage) srvPage.style.display = '';
  },

  backFromServer() {
    // 隐藏服务器页面
    const srvPage = document.getElementById('page-server');
    if (srvPage) srvPage.style.display = 'none';

    // 显示测速页
    const speedPage = document.getElementById('page-speed');
    if (speedPage) speedPage.style.display = '';

    // 更新导航
    this.curPage = 'speed';
    document.querySelectorAll('.nav-item').forEach(el => {
      el.classList.toggle('active', el.dataset.page === 'speed');
    });
  },

  // ==================== 服务器选择 ====================
  selectServer(id) {
    const s = ServerData.getById(id);
    if (!s) return;
    this.server = s;
    const el = this.$['srv-name'];
    if (el) el.innerHTML = s.name + ' <span class="info-arrow">&#9664;</span>';
    this.renderServerList();
    this.backFromServer();
  },

  renderServerList() {
    const container = this.$['server-list'];
    if (!container) return;
    const groups = ServerData.getGrouped();

    container.innerHTML = Object.entries(groups).map(([region, list]) => {
      // 判断当前选中服务器是否在此省份中
      const hasSelected = this.server && list.some(s => s.id === this.server.id);
      return '<div class="server-group' + (hasSelected ? ' expanded' : '') + '">' +
        '<div class="server-group-title" onclick="app.toggleRegion(this)">' +
          '<span>' + region + '</span>' +
          '<svg class="group-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>' +
        '</div>' +
        '<div class="server-group-body">' +
        list.map(s => {
          const sel = this.server && this.server.id === s.id;
          return '<div class="server-option' + (sel ? ' selected' : '') + '" onclick="app.selectServer(' + s.id + ')">' +
            '<div class="server-info"><div class="server-name">' + s.name + '</div></div>' +
            '<div class="server-check' + (sel ? ' checked' : '') + '">' +
              (sel ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>' : '') +
            '</div>' +
            '</div>';
        }).join('') +
        '</div></div>';
    }).join('');
  },

  toggleRegion(el) {
    const group = el.closest('.server-group');
    group.classList.toggle('expanded');
  },

  // ==================== 工具 ====================
  showToast(msg) {
    const t = this.$['toast'];
    if (!t) return;
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
  }
};

document.addEventListener('DOMContentLoaded', () => app.init());
