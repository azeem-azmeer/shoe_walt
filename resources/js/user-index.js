/**
 * Home page Alpine components
 * Path: resources/js/user-index.js
 */

document.addEventListener('alpine:init', () => {
  // Carousel / hero slider
  Alpine.data('heroSlider', (opts = {}) => ({
    index: 0,
    images: Array.isArray(opts.images) ? opts.images : [],
    timer: null,
    interval: Number(opts.interval) || 5000,

    start() {
      this.stop();
      if (this.images.length > 1) {
        this.timer = setInterval(() => this.next(), this.interval);
      }
    },
    stop() {
      if (this.timer) { clearInterval(this.timer); this.timer = null; }
    },
    next() {
      if (!this.images.length) return;
      this.index = (this.index + 1) % this.images.length;
    },
    prev() {
      if (!this.images.length) return;
      this.index = (this.index - 1 + this.images.length) % this.images.length;
    },
    key(e) {
      if (e.key === 'ArrowRight') this.next();
      if (e.key === 'ArrowLeft')  this.prev();
    },
  }));

  // Horizontal scroller used by cards/sections
  Alpine.data('cardScroller', () => ({
    get stepPx() {
      const pct  = parseFloat(this.$root.dataset.step || '0.8');
      const safe = isNaN(pct) ? 0.8 : Math.max(0.2, Math.min(1, pct));
      return Math.round(window.innerWidth * safe);
    },
    next() { this.$refs.wrap?.scrollBy({ left:  this.stepPx, behavior: 'smooth' }); },
    prev() { this.$refs.wrap?.scrollBy({ left: -this.stepPx, behavior: 'smooth' }); },
  }));
});
