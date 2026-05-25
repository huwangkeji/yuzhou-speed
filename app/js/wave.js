/**
 * 波形图渲染模块
 * 作者: 抖音@NCYXF
 */

class WaveChart {
  constructor(canvas, color = '#4A9DFF') {
    this.canvas = canvas;
    this.ctx = canvas.getContext('2d');
    this.color = color;
    this.data = [];
    this.maxPoints = 40;
  }

  add(value) {
    this.data.push(value);
    if (this.data.length > this.maxPoints) this.data.shift();
    this.draw();
  }

  clear() {
    this.data = [];
    this.draw();
  }

  draw() {
    const ctx = this.ctx;
    const w = this.canvas.width;
    const h = this.canvas.height;
    ctx.clearRect(0, 0, w, h);
    if (this.data.length < 2) return;

    const maxVal = Math.max(...this.data, 10);
    const pad = 3;
    const chartH = h - pad * 2;

    // 填充区域
    ctx.beginPath();
    ctx.moveTo(0, h);
    this.data.forEach((v, i) => {
      const px = (i / (this.maxPoints - 1)) * w;
      const py = pad + chartH - (v / maxVal) * chartH;
      if (i === 0) {
        ctx.lineTo(px, py);
      } else {
        const pp = ((i - 1) / (this.maxPoints - 1)) * w;
        const ppy = pad + chartH - (this.data[i - 1] / maxVal) * chartH;
        const cpx = (pp + px) / 2;
        ctx.bezierCurveTo(cpx, ppy, cpx, py, px, py);
      }
    });
    ctx.lineTo(w, h);
    ctx.closePath();

    const grad = ctx.createLinearGradient(0, 0, 0, h);
    grad.addColorStop(0, this.color + '35');
    grad.addColorStop(1, this.color + '05');
    ctx.fillStyle = grad;
    ctx.fill();

    // 线条
    ctx.beginPath();
    this.data.forEach((v, i) => {
      const px = (i / (this.maxPoints - 1)) * w;
      const py = pad + chartH - (v / maxVal) * chartH;
      if (i === 0) {
        ctx.moveTo(px, py);
      } else {
        const pp = ((i - 1) / (this.maxPoints - 1)) * w;
        const ppy = pad + chartH - (this.data[i - 1] / maxVal) * chartH;
        const cpx = (pp + px) / 2;
        ctx.bezierCurveTo(cpx, ppy, cpx, py, px, py);
      }
    });
    ctx.strokeStyle = this.color;
    ctx.lineWidth = 1.8;
    ctx.stroke();
  }
}
