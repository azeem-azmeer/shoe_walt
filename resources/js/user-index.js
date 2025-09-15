// resources/js/user-index.js
// Do NOT re-initialize Alpine here; app.js already does that.

window.heroSlider = (opts = {}) => {
  const images = Array.isArray(opts.images) ? opts.images : [];
  const interval = Number(opts.interval) || 5000;

  return {
    images,
    index: 0,
    timer: null,
    start() { this.stop(); if (this.images.length > 1) this.timer = setInterval(() => this.next(), interval); },
    stop()  { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
    next()  { if (!this.images.length) return; this.index = (this.index + 1) % this.images.length; },
    prev()  { if (!this.images.length) return; this.index = (this.index - 1 + this.images.length) % this.images.length; },
    key(e)  { if (e.key === 'ArrowLeft') this.prev(); if (e.key === 'ArrowRight') this.next(); },
  };
};

window.cardScroller = () => ({
  step: 0,
  init() {
    this.$nextTick(() => {
      const wrap = this.$refs.wrap;
      const first = wrap?.firstElementChild;
      const gap = parseInt(getComputedStyle(wrap).columnGap || getComputedStyle(wrap).gap || '16', 10);
      this.step = (first?.offsetWidth || 320) + (gap || 16);
    });
  },
  prev() { this.$refs.wrap?.scrollBy({ left: -this.step, behavior: 'smooth' }); },
  next() { this.$refs.wrap?.scrollBy({ left:  this.step, behavior: 'smooth' }); },
});
