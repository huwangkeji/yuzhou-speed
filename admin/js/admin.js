/**
 * 宇宙测速 - 管理后台JS
 * 作者: 抖音@NCYXF
 * 支持PHP预加载数据(window.PRELOADED)，避免白屏闪烁
 */

const API_BASE = '../api/admin/';
const API_PUBLIC = '../api/';

const admin = {
    token: localStorage.getItem('admin_token') || '',
    currentPage: 'dashboard',
    servers: [],
    serverPage: 1,
    serverLimit: 20,
    serverTotal: 0,
    editingServer: null,

    init() {
        // PHP已做登录校验，前端兜底检查
        if (!this.token) {
            window.location.href = 'login.php';
            return;
        }

        // 优先使用PHP预加载数据，避免白屏闪烁
        if (window.PRELOADED) {
            this.applyPreloaded(window.PRELOADED);
        } else {
            // 纯静态降级：AJAX加载
            this.loadStats();
            this.loadServers();
            this.loadSettings();
        }
        this.bindEvents();
    },

    /**
     * 应用PHP预加载数据
     */
    applyPreloaded(data) {
        // 1. 统计数据 — PHP已直接输出到HTML，无需JS更新
        if (data.stats) {
            const el = (id) => document.getElementById(id);
            if (el('stat-server-count')) el('stat-server-count').textContent = data.stats.serverCount || 0;
            if (el('stat-test-count')) el('stat-test-count').textContent = data.stats.testCount || 0;
            if (el('stat-avg-speed')) el('stat-avg-speed').textContent = data.stats.avgSpeed || 0;
        }

        // 2. 服务器列表
        if (data.servers) {
            this.servers = data.servers;
            this.serverTotal = data.serverTotal || data.servers.length;
            this.renderServerTable();
            this.renderPagination();
        }

        // 3. 设置数据
        if (data.settings) {
            this.renderSettings(data.settings);
        }
    },

    async request(url, options = {}) {
        const opts = {
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };
        if (options.body && typeof options.body === 'object') {
            opts.body = JSON.stringify(options.body);
        }
        const res = await fetch(url, opts);
        const data = await res.json();
        if (data.code === 401) {
            localStorage.removeItem('admin_token');
            window.location.href = 'login.php';
            return null;
        }
        return data;
    },

    // ===== 页面切换 =====
    switchPage(page) {
        document.querySelectorAll('.admin-page').forEach(el => el.classList.remove('active'));
        document.getElementById('page-' + page)?.classList.add('active');
        document.querySelectorAll('.nav-link').forEach(el => el.classList.toggle('active', el.dataset.page === page));
        this.currentPage = page;
    },

    // ===== 仪表盘 =====
    async loadStats() {
        try {
            const serverRes = await this.request(API_BASE + 'servers.php');
            if (serverRes?.code === 200) {
                document.getElementById('stat-server-count').textContent = serverRes.pagination?.total || 0;
            }

            const historyRes = await fetch(API_PUBLIC + 'history.php');
            const historyData = await historyRes.json();
            if (historyData.code === 200) {
                const list = historyData.data || [];
                document.getElementById('stat-test-count').textContent = list.length;
                const avg = list.length > 0
                    ? (list.reduce((s, h) => s + (parseFloat(h.download_speed) || 0), 0) / list.length).toFixed(1)
                    : 0;
                document.getElementById('stat-avg-speed').textContent = avg;
            }
        } catch (e) {
            console.error('加载统计失败:', e);
        }
    },

    // ===== 服务器管理 =====
    async loadServers(page = 1) {
        this.serverPage = page;
        try {
            const res = await this.request(API_BASE + 'servers.php?page=' + page + '&limit=' + this.serverLimit);
            if (res?.code === 200) {
                this.servers = res.data;
                this.serverTotal = res.pagination?.total || 0;
                this.renderServerTable();
                this.renderPagination();
            }
        } catch (e) {
            console.error('加载服务器失败:', e);
        }
    },

    renderServerTable() {
        const tbody = document.getElementById('server-table-body');
        if (!tbody) return;

        if (!this.servers.length) {
            tbody.innerHTML = '<tr><td colspan="12" style="text-align:center;color:#8892b0;padding:40px;">暂无数据</td></tr>';
            return;
        }

        tbody.innerHTML = this.servers.map(sv => `
            <tr>
                <td>${sv.id}</td>
                <td>${sv.name}</td>
                <td>${sv.region || '-'}</td>
                <td>${sv.city || '-'}</td>
                <td>${sv.operator || '-'}</td>
                <td>${sv.download_speed} Mbps</td>
                <td>${sv.upload_speed} Mbps</td>
                <td>±${sv.speed_fluctuation}%</td>
                <td>${sv.ping_value}ms</td>
                <td>${sv.test_duration}s</td>
                <td><span class="status-badge ${sv.status == 1 ? 'enabled' : 'disabled'}">${sv.status == 1 ? '启用' : '禁用'}</span></td>
                <td>
                    <button class="btn-edit" onclick="admin.editServer(${sv.id})">编辑</button>
                    <button class="btn-danger" onclick="admin.deleteServer(${sv.id})">删除</button>
                </td>
            </tr>
        `).join('');
    },

    renderPagination() {
        const container = document.getElementById('server-pagination');
        if (!container) return;
        const pages = Math.ceil(this.serverTotal / this.serverLimit);
        if (pages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        html += `<button ${this.serverPage <= 1 ? 'disabled' : ''} onclick="admin.loadServers(${this.serverPage - 1})">上一页</button>`;
        for (let i = 1; i <= pages; i++) {
            if (i === 1 || i === pages || (i >= this.serverPage - 1 && i <= this.serverPage + 1)) {
                html += `<button class="${i === this.serverPage ? 'active' : ''}" onclick="admin.loadServers(${i})">${i}</button>`;
            } else if (i === this.serverPage - 2 || i === this.serverPage + 2) {
                html += `<span style="color:#8892b0;padding:0 4px;">...</span>`;
            }
        }
        html += `<button ${this.serverPage >= pages ? 'disabled' : ''} onclick="admin.loadServers(${this.serverPage + 1})">下一页</button>`;
        container.innerHTML = html;
    },

    openServerModal(server = null) {
        this.editingServer = server;
        const modal = document.getElementById('server-modal');
        const title = document.getElementById('modal-title');
        const form = document.getElementById('server-form');

        title.textContent = server ? '编辑服务器' : '添加服务器';
        form.reset();
        document.getElementById('server-id').value = server ? server.id : '';

        if (server) {
            document.getElementById('sv-name').value = server.name || '';
            document.getElementById('sv-region').value = server.region || '';
            document.getElementById('sv-city').value = server.city || '';
            document.getElementById('sv-operator').value = server.operator || '';
            document.getElementById('sv-download').value = server.download_speed || 100;
            document.getElementById('sv-upload').value = server.upload_speed || 50;
            document.getElementById('sv-fluctuation').value = server.speed_fluctuation || 10;
            document.getElementById('sv-ping').value = server.ping_value || 15;
            document.getElementById('sv-jitter').value = server.jitter_value || 5;
            document.getElementById('sv-loss').value = server.packet_loss || 0;
            document.getElementById('sv-duration').value = server.test_duration || 5;
            document.getElementById('sv-sort').value = server.sort_order || 0;
            document.getElementById('sv-status').checked = server.status == 1;
        }

        modal.classList.add('show');
    },

    closeServerModal() {
        document.getElementById('server-modal').classList.remove('show');
        this.editingServer = null;
    },

    async saveServer(e) {
        e.preventDefault();
        const id = document.getElementById('server-id').value;
        const data = {
            name: document.getElementById('sv-name').value,
            region: document.getElementById('sv-region').value,
            city: document.getElementById('sv-city').value,
            operator: document.getElementById('sv-operator').value,
            download_speed: parseFloat(document.getElementById('sv-download').value) || 100,
            upload_speed: parseFloat(document.getElementById('sv-upload').value) || 50,
            speed_fluctuation: parseFloat(document.getElementById('sv-fluctuation').value) || 10,
            ping_value: parseInt(document.getElementById('sv-ping').value) || 15,
            jitter_value: parseInt(document.getElementById('sv-jitter').value) || 5,
            packet_loss: parseFloat(document.getElementById('sv-loss').value) || 0,
            test_duration: parseInt(document.getElementById('sv-duration').value) || 5,
            sort_order: parseInt(document.getElementById('sv-sort').value) || 0,
            status: document.getElementById('sv-status').checked ? 1 : 0
        };

        try {
            let res;
            if (id) {
                data.id = parseInt(id);
                res = await this.request(API_BASE + 'servers.php', {
                    method: 'PUT',
                    body: data
                });
            } else {
                res = await this.request(API_BASE + 'servers.php', {
                    method: 'POST',
                    body: data
                });
            }

            if (res?.code === 200) {
                this.closeServerModal();
                this.loadServers(this.serverPage);
                this.loadStats();
            } else {
                alert(res?.error || '保存失败');
            }
        } catch (e) {
            alert('保存失败: ' + e.message);
        }
    },

    async editServer(id) {
        const server = this.servers.find(s => s.id == id);
        if (server) this.openServerModal(server);
    },

    async deleteServer(id) {
        if (!confirm('确定要删除此服务器吗？')) return;
        try {
            const res = await this.request(API_BASE + 'servers.php?id=' + id, {
                method: 'DELETE'
            });
            if (res?.code === 200) {
                this.loadServers(this.serverPage);
                this.loadStats();
            } else {
                alert(res?.error || '删除失败');
            }
        } catch (e) {
            alert('删除失败: ' + e.message);
        }
    },

    // ===== 设置 =====
    async loadSettings() {
        try {
            const res = await fetch(API_PUBLIC + 'settings.php');
            const data = await res.json();
            if (data.code === 200) {
                this.renderSettings(data.data);
            }
        } catch (e) {
            console.error('加载设置失败:', e);
        }
    },

    renderSettings(settings) {
        const container = document.getElementById('settings-list');
        if (!container) return;

        const configDefs = [
            { key: 'site_name', label: '网站名称', type: 'text', placeholder: '宇宙测速' },
            { key: 'ip_api_url', label: 'IP查询API地址', type: 'text', placeholder: '如: https://api.xxapi.cn/api/ip?ip={ip}' },
            { key: 'app_version', label: '应用版本', type: 'text', placeholder: 'v3.0.0' },
            { key: 'download_test_duration', label: '下载测试时长(秒)', type: 'number', placeholder: '10' },
            { key: 'upload_test_duration', label: '上传测试时长(秒)', type: 'number', placeholder: '10' },
            { key: 'dl_ul_interval', label: '下行→上行间隔(秒)', type: 'number', placeholder: '3' },
            { key: 'ping_test_count', label: 'Ping测试次数', type: 'number', placeholder: '5' },
            { key: 'max_history_records', label: '最大历史记录数', type: 'number', placeholder: '100' },
            { key: 'theme_color', label: '主题色', type: 'text', placeholder: '#4A9DFF' }
        ];

        let html = configDefs.map(def => `
            <div class="setting-row">
                <label>${def.label}</label>
                <input type="${def.type}" value="${settings?.[def.key] ?? ''}" data-key="${def.key}" placeholder="${def.placeholder}">
            </div>
        `).join('');

        html += `<div class="setting-actions" style="margin-top:16px;text-align:right;">
            <button class="btn-primary" onclick="admin.saveSettings()">保存设置</button>
        </div>`;

        container.innerHTML = html;
    },

    async saveSettings() {
        const inputs = document.querySelectorAll('#settings-list input[data-key]');
        const data = {};
        inputs.forEach(input => {
            if (input.value.trim() !== '') {
                data[input.dataset.key] = input.value.trim();
            }
        });

        if (Object.keys(data).length === 0) {
            alert('没有要保存的更改');
            return;
        }

        try {
            const res = await this.request(API_PUBLIC + 'settings.php', {
                method: 'PUT',
                body: data
            });
            if (res?.code === 200) {
                alert('保存成功');
                this.loadSettings();
            } else {
                alert(res?.error || '保存失败');
            }
        } catch (e) {
            alert('保存失败: ' + e.message);
        }
    },

    // ===== 退出 =====
    async logout() {
        try {
            await this.request(API_BASE + 'auth.php', { method: 'DELETE' });
        } catch (e) {}
        localStorage.removeItem('admin_token');
        window.location.href = 'login.php';
    },

    bindEvents() {
        document.getElementById('server-form')?.addEventListener('submit', (e) => this.saveServer(e));
    }
};

document.addEventListener('DOMContentLoaded', () => admin.init());
