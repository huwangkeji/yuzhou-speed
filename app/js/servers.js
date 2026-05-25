/**
 * 服务器数据模块
 * 作者: 抖音@NCYXF
 * 优先使用PHP从数据库注入的 SERVER_DATA，无数据时使用静态兜底
 */

const ServerData = {
  // 优先级: PHP注入(window.SERVER_DATA) > 静态兜底
  _servers: null,

  _getServers() {
    if (this._servers !== null) return this._servers;

    // PHP从数据库注入的服务器列表
    if (window.SERVER_DATA && Array.isArray(window.SERVER_DATA) && window.SERVER_DATA.length > 0) {
      this._servers = window.SERVER_DATA.map(s => ({
        id: parseInt(s.id),
        name: s.name || '',
        region: s.region || '',
        city: s.city || '',
        operator: s.operator || '',
        download_speed: parseInt(s.download_speed) || 500,
        upload_speed: parseInt(s.upload_speed) || 200,
        speed_fluctuation: parseInt(s.speed_fluctuation) || 10,
        ping_value: parseFloat(s.ping_value) || 20,
        jitter_value: parseFloat(s.jitter_value) || 5,
        packet_loss: parseFloat(s.packet_loss) || 0,
        test_duration: parseInt(s.test_duration) || 10,
        status: parseInt(s.status) || 1,
        is_recommended: parseInt(s.is_recommended) || 0
      }));
      return this._servers;
    }

    // 静态兜底数据（无PHP环境或数据库为空时使用）
    this._servers = [
      { id: 1, name: '石家庄移动', region: '河北', city: '石家庄', operator: '移动', download_speed: 500, upload_speed: 200, ping_value: 12, jitter_value: 3, packet_loss: 0 },
      { id: 2, name: '北京电信', region: '北京', city: '北京', operator: '电信', download_speed: 800, upload_speed: 300, ping_value: 8, jitter_value: 2, packet_loss: 0 },
      { id: 3, name: '上海联通', region: '上海', city: '上海', operator: '联通', download_speed: 600, upload_speed: 250, ping_value: 10, jitter_value: 2, packet_loss: 0 },
      { id: 4, name: '广州移动', region: '广东', city: '广州', operator: '移动', download_speed: 700, upload_speed: 280, ping_value: 14, jitter_value: 3, packet_loss: 0 },
      { id: 5, name: '成都电信', region: '四川', city: '成都', operator: '电信', download_speed: 550, upload_speed: 220, ping_value: 18, jitter_value: 4, packet_loss: 0 }
    ];
    return this._servers;
  },

  getAll() {
    return this._getServers();
  },

  getById(id) {
    const list = this._getServers();
    return list.find(s => s.id === id) || list[0];
  },

  getGrouped() {
    const groups = {};
    this._getServers().forEach(s => {
      const region = s.region || '其他';
      if (!groups[region]) groups[region] = [];
      groups[region].push(s);
    });
    return groups;
  }
};
