// resources/js/bootstrap.js

import _ from 'lodash';
window._ = _;

import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ⬇️ Replace the existing 419 handler with this:
window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;

    if (status === 419) {
      // Silently refresh the page once (no confirm dialog)
      if (!window.__reloadedFor419) {
        window.__reloadedFor419 = true;
        window.location.reload();
      }
      // Don’t propagate the error; it’s handled.
      return Promise.resolve();
    }

    return Promise.reject(error);
  }
);
