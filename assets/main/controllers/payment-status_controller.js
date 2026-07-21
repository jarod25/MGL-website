import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    url: String,
    interval: { type: Number, default: 4500 },
    maxFailures: { type: Number, default: 6 },
    checkingText: String,
    temporaryErrorText: String,
  };

  static targets = ['status', 'retry'];

  connect() {
    this.failures = 0;
    this.tick = this.tick.bind(this);
    this.visibility = this.visibility.bind(this);

    document.addEventListener('visibilitychange', this.visibility);
    this.start();
  }

  disconnect() {
    this.stop();
    document.removeEventListener('visibilitychange', this.visibility);
  }

  visibility() {
    if (document.hidden) {
      this.stop();

      return;
    }

    this.start();
  }

  start() {
    if (this.timer || document.hidden) {
      return;
    }

    this.timer = setTimeout(this.tick, 500);
  }

  stop() {
    clearTimeout(this.timer);
    this.timer = null;
  }

  async tick() {
    this.timer = null;

    try {
      const response = await fetch(this.urlValue, {
        headers: { Accept: 'application/json' },
      });

      if (!response.ok) {
        throw new Error('status');
      }

      const data = await response.json();
      this.failures = 0;

      if (data.paid && data.redirectUrl) {
        window.location.assign(data.redirectUrl);

        return;
      }

      this.setStatus(this.checkingTextValue || 'Checking payment status…');
      this.timer = setTimeout(this.tick, this.intervalValue);
    } catch (error) {
      this.failures++;
      this.setStatus(this.temporaryErrorTextValue || 'Temporary verification error.');

      if (this.failures >= this.maxFailuresValue) {
        if (this.hasRetryTarget) {
          this.retryTarget.hidden = false;
        }

        return;
      }

      this.timer = setTimeout(this.tick, this.intervalValue + (this.failures * 2000));
    }
  }

  retry() {
    this.failures = 0;

    if (this.hasRetryTarget) {
      this.retryTarget.hidden = true;
    }

    this.stop();
    this.start();
  }

  setStatus(text) {
    if (this.hasStatusTarget) {
      this.statusTarget.textContent = text;
    }
  }
}
