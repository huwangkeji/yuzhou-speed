/**
 * 测速逻辑模块
 * 作者: 抖音@NCYXF
 */

class SpeedTester {
  constructor(options = {}) {
    this.onPingUpdate = options.onPingUpdate || (() => {});
    this.onSpeedUpdate = options.onSpeedUpdate || (() => {});
    this.onPhaseChange = options.onPhaseChange || (() => {});
    this.onComplete = options.onComplete || (() => {});
    this.onInterval = options.onInterval || (() => {}); // 间隔倒计时回调
    this.aborted = false;
    this.dlUlInterval = options.dlUlInterval || 3; // 下行→上行间隔秒数
  }

  abort() {
    this.aborted = true;
  }

  async run(server) {
    this.aborted = false;

    // Phase 1: Ping
    this.onPhaseChange('ping');
    const pingResult = await this.testPing(server);
    if (this.aborted) return null;

    // Phase 2: Download
    this.onPhaseChange('download');
    const dlResult = await this.testSpeed(server, 'download');
    if (this.aborted) return null;

    // 下行→上行间隔等待
    if (this.dlUlInterval > 0) {
      this.onPhaseChange('interval');
      for (let i = this.dlUlInterval; i > 0; i--) {
        if (this.aborted) return null;
        this.onInterval(i);
        await this.sleep(1000);
      }
    }

    // Phase 3: Upload
    this.onPhaseChange('upload');
    const ulResult = await this.testSpeed(server, 'upload');
    if (this.aborted) return null;

    // Complete
    this.onPhaseChange('complete');
    this.onComplete({
      ping: pingResult,
      download: dlResult,
      upload: ulResult
    });

    return {
      ping: pingResult,
      download: dlResult,
      upload: ulResult
    };
  }

  async testPing(server) {
    const base = server.ping_value || 12;
    const results = [];
    for (let i = 0; i < 7; i++) {
      if (this.aborted) return null;
      const val = Math.max(1, base + Math.floor(Math.random() * 8 - 4));
      results.push(val);
      this.onPingUpdate(val);
      await this.sleep(300);
    }
    return {
      avg: Math.round(results.reduce((a, b) => a + b, 0) / results.length),
      min: Math.min(...results),
      max: Math.max(...results)
    };
  }

  async testSpeed(server, type) {
    const baseSpeed = type === 'download'
      ? (server.download_speed || 500)
      : (server.upload_speed || 200);
    const duration = 6; // 秒
    const pps = 5; // 每秒采样点
    const totalPoints = duration * pps;
    const fluctuation = 0.1;

    let currentSpeed = 0;
    let maxSpeed = 0;
    let speedSum = 0;
    let pointCount = 0;

    for (let i = 0; i < totalPoints; i++) {
      if (this.aborted) return null;

      const progress = i / totalPoints;
      let factor;

      // 速度曲线: 快速加速 → 爬升 → 稳定波动
      if (progress < 0.15) {
        factor = (progress / 0.15) * 0.55;
      } else if (progress < 0.4) {
        factor = 0.55 + (progress - 0.15) / 0.25 * 0.35;
      } else {
        factor = 0.9 + Math.sin(progress * 15) * 0.06;
      }

      currentSpeed = Math.max(0.5, baseSpeed * factor * (1 + (Math.random() - 0.5) * fluctuation * 2));
      currentSpeed = Math.round(currentSpeed * 100) / 100;
      maxSpeed = Math.max(maxSpeed, currentSpeed);
      speedSum += currentSpeed;
      pointCount++;

      this.onSpeedUpdate(type, currentSpeed);
      await this.sleep(200);
    }

    return {
      avg: pointCount > 0 ? Math.round(speedSum / pointCount * 100) / 100 : 0,
      max: Math.round(maxSpeed * 100) / 100
    };
  }

  sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}
