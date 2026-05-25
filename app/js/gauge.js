/**
 * 仪表盘渲染模块
 * 作者: 抖音@NCYXF
 */

class SpeedGauge {
  constructor(canvas) {
    this.canvas = canvas;
    this.ctx = canvas.getContext('2d');
    this.maxValue = 5000;
    this.smoothValue = 0;
    this.targetValue = 0;
    this.animFrame = null;

    // 刻度定义: 对数分布 0 → 5G
    this.scaleData = [
      { label: '0', val: 0 },
      { label: '5', val: 5 },
      { label: '10', val: 10 },
      { label: '20', val: 20 },
      { label: '50', val: 50 },
      { label: '100', val: 100 },
      { label: '200', val: 200 },
      { label: '500', val: 500 },
      { label: '1G', val: 1000 },
      { label: '2G', val: 2000 },
      { label: '5G', val: 5000 }
    ];

    this.startAngle = Math.PI * 0.78;
    this.endAngle = Math.PI * 2.22;
    this.totalAngle = this.endAngle - this.startAngle;
  }

  // 对数映射: 数值 → 角度
  valueToAngle(v) {
    if (v <= 0) return this.startAngle;
    const t = Math.log(v + 1) / Math.log(this.maxValue + 1);
    return this.startAngle + t * this.totalAngle;
  }

  draw(value) {
    this.targetValue = Math.max(0, Math.min(value, this.maxValue));
    this.animateTo();
  }

  animateTo() {
    if (this.animFrame) cancelAnimationFrame(this.animFrame);
    const step = () => {
      const diff = this.targetValue - this.smoothValue;
      this.smoothValue += diff * 0.15;
      if (Math.abs(diff) < 0.2) this.smoothValue = this.targetValue;
      this.render();
      if (Math.abs(diff) > 0.2) {
        this.animFrame = requestAnimationFrame(step);
      }
    };
    step();
  }

  render() {
    const ctx = this.ctx;
    const w = this.canvas.width;
    const h = this.canvas.height;
    const cx = w / 2;
    const cy = h / 2 + 10;
    const r = Math.min(w, h) / 2 - 18;

    ctx.clearRect(0, 0, w, h);

    const valAngle = this.valueToAngle(this.smoothValue);

    // ── 外圈背景弧 ──
    ctx.beginPath();
    ctx.arc(cx, cy, r, this.startAngle, this.endAngle);
    ctx.strokeStyle = '#1e2744';
    ctx.lineWidth = 14;
    ctx.lineCap = 'round';
    ctx.stroke();

    // ── 刻度线和数字 ──
    this.scaleData.forEach((sd) => {
      const angle = this.valueToAngle(sd.val);
      const innerR = r - 10;
      const outerR = r + 3;

      ctx.beginPath();
      ctx.moveTo(cx + Math.cos(angle) * innerR, cy + Math.sin(angle) * innerR);
      ctx.lineTo(cx + Math.cos(angle) * outerR, cy + Math.sin(angle) * outerR);
      ctx.strokeStyle = '#3a4a6a';
      ctx.lineWidth = 1.5;
      ctx.stroke();

      // 刻度数字
      const lr = r + 20;
      ctx.fillStyle = '#7a8aaa';
      ctx.font = '500 10px -apple-system, sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';

      let tx = cx + Math.cos(angle) * lr;
      let ty = cy + Math.sin(angle) * lr;

      // 底部刻度位置微调
      const deg = (angle * 180 / Math.PI) % 360;
      if (deg > 330 || deg < 30) { tx += 2; ty += 4; }
      else if (deg > 150 && deg < 210) { tx -= 2; }

      ctx.fillText(sd.label, tx, ty);
    });

    // ── 小刻度 ──
    for (let i = 0; i < this.scaleData.length - 1; i++) {
      const a1 = this.valueToAngle(this.scaleData[i].val);
      const a2 = this.valueToAngle(this.scaleData[i + 1].val);
      for (let j = 1; j <= 4; j++) {
        const angle = a1 + (a2 - a1) * (j / 5);
        ctx.beginPath();
        ctx.moveTo(cx + Math.cos(angle) * (r - 4), cy + Math.sin(angle) * (r - 4));
        ctx.lineTo(cx + Math.cos(angle) * (r - 1), cy + Math.sin(angle) * (r - 1));
        ctx.strokeStyle = '#2a3a5a';
        ctx.lineWidth = 1;
        ctx.stroke();
      }
    }

    // ── 进度弧 ──
    if (this.smoothValue > 1) {
      const grad = ctx.createLinearGradient(cx - r, cy - r, cx + r, cy + r);
      grad.addColorStop(0, '#3A9DFF');
      grad.addColorStop(0.5, '#4AADFF');
      grad.addColorStop(1, '#5ABDFF');

      ctx.save();
      ctx.beginPath();
      ctx.arc(cx, cy, r, this.startAngle, valAngle);
      ctx.strokeStyle = grad;
      ctx.lineWidth = 14;
      ctx.lineCap = 'round';
      ctx.shadowColor = '#4A9DFF';
      ctx.shadowBlur = 12;
      ctx.stroke();
      ctx.restore();

      // 末端亮点
      const tipX = cx + Math.cos(valAngle) * r;
      const tipY = cy + Math.sin(valAngle) * r;
      const tg = ctx.createRadialGradient(tipX, tipY, 0, tipX, tipY, 10);
      tg.addColorStop(0, 'rgba(74,157,255,0.7)');
      tg.addColorStop(1, 'rgba(74,157,255,0)');
      ctx.beginPath();
      ctx.arc(tipX, tipY, 10, 0, Math.PI * 2);
      ctx.fillStyle = tg;
      ctx.fill();
    }

    // ── 指针 ──
    const needleAngle = this.smoothValue > 1 ? valAngle : this.valueToAngle(2);
    const needleLen = r - 20;
    const nx = cx + Math.cos(needleAngle) * needleLen;
    const ny = cy + Math.sin(needleAngle) * needleLen;

    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.lineTo(nx, ny);
    ctx.strokeStyle = this.smoothValue > 1 ? '#ccd6f6' : '#7a8aaa';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.stroke();

    // 中心圆
    ctx.beginPath();
    ctx.arc(cx, cy, 6, 0, Math.PI * 2);
    ctx.fillStyle = '#141824';
    ctx.fill();
    ctx.beginPath();
    ctx.arc(cx, cy, 6, 0, Math.PI * 2);
    ctx.strokeStyle = this.smoothValue > 1 ? '#4A9DFF' : '#5a6a8a';
    ctx.lineWidth = 1.5;
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(cx, cy, 2.5, 0, Math.PI * 2);
    ctx.fillStyle = this.smoothValue > 1 ? '#4A9DFF' : '#5a6a8a';
    ctx.fill();
  }
}
