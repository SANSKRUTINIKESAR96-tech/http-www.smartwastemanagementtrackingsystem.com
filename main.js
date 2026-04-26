/* =====================================================================
   EcoTrack – global client-side helpers (AJAX, form handling, charts)
   ===================================================================== */

(function () {
  'use strict';

  // Highlight the current sidebar link.
  document.addEventListener('DOMContentLoaded', function () {
    const here = location.pathname.replace(/\/+$/, '');
    document.querySelectorAll('.sidebar nav a').forEach(a => {
      const href = a.getAttribute('href') || '';
      if (href && here.endsWith(href.split('/').slice(-2).join('/'))) {
        a.classList.add('active');
      }
    });

    // Attach AJAX handlers to any form with .js-ajax
    document.querySelectorAll('form.js-ajax').forEach(bindAjaxForm);

    // Confirm dialogs
    document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener('click', ev => {
        if (!window.confirm(el.dataset.confirm)) ev.preventDefault();
      });
    });
  });

  /**
   * Submit a form via fetch() as JSON, expecting { ok: bool, message, redirect? }.
   * Displays .form-message inside the form for feedback.
   */
  function bindAjaxForm(form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = form.querySelector('button[type=submit]');
      const msg = ensureMsg(form);
      msg.textContent = '';
      msg.className = 'form-message';
      const origText = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Please wait…'; }

      try {
        const fd = new FormData(form);
        const res = await fetch(form.action, {
          method: form.method || 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'fetch' }
        });
        const data = await res.json().catch(() => ({ ok: false, message: 'Invalid server response' }));

        if (data.ok) {
          msg.textContent = data.message || 'Saved.';
          msg.className = 'alert alert-success';
          if (data.redirect) setTimeout(() => location.href = data.redirect, 500);
          else if (form.dataset.reload !== 'false') setTimeout(() => location.reload(), 700);
        } else {
          msg.textContent = data.message || 'Something went wrong.';
          msg.className = 'alert alert-error';
        }
      } catch (err) {
        msg.textContent = 'Network error. Please try again.';
        msg.className = 'alert alert-error';
      } finally {
        if (btn) { btn.disabled = false; btn.textContent = origText; }
      }
    });
  }

  function ensureMsg(form) {
    let msg = form.querySelector('.form-message');
    if (!msg) {
      msg = document.createElement('div');
      msg.className = 'form-message';
      form.prepend(msg);
    }
    return msg;
  }

  /** Exposed: fire-and-forget AJAX call used for status/delete actions. */
  window.ecoRequest = async function (url, data = {}, method = 'POST') {
    const fd = new FormData();
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const token = (document.querySelector('input[name="_csrf"]') || {}).value || '';
    if (token) fd.append('_csrf', token);
    const res = await fetch(url, { method, body: fd, headers: { 'X-Requested-With': 'fetch' } });
    return res.json();
  };

  /** Render a chart if Chart.js is loaded. */
  window.ecoChart = function (canvasId, config) {
    const wait = () => {
      if (typeof Chart === 'undefined') { return setTimeout(wait, 50); }
      const el = document.getElementById(canvasId);
      if (!el) return;
      new Chart(el.getContext('2d'), config);
    };
    wait();
  };
})();
