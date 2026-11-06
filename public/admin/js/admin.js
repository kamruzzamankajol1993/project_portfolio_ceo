document.querySelectorAll("[data-password-toggle]").forEach((button) => {
  button.addEventListener("click", () => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    button.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    button.setAttribute("aria-label", show ? "Hide password" : "Show password");
  });
});

// Poll for customer registrations while an administrator is using the panel.
(function () {
  'use strict';

  const bell = document.querySelector('[data-customer-notifications]');
  if (!bell) return;

  const badge = bell.querySelector('[data-notification-badge]');
  const endpoint = bell.dataset.endpoint;
  const viewUrl = bell.dataset.viewUrl;
  let afterId = Number.parseInt(bell.dataset.afterId || '0', 10) || 0;
  let polling = false;

  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[character]);

  function updateBadge(count) {
    if (!badge) return;
    const safeCount = Math.max(0, Number(count) || 0);
    badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
    badge.hidden = safeCount === 0;
    bell.setAttribute('aria-label', safeCount ? `${safeCount} customer accounts awaiting attention` : 'Customer registration notifications');
  }

  async function pollRegistrations() {
    if (polling || document.hidden || !endpoint) return;
    polling = true;

    try {
      const url = new URL(endpoint, window.location.href);
      url.searchParams.set('after_id', String(afterId));
      const response = await fetch(url.toString(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!response.ok) return;

      const data = await response.json();
      updateBadge(data.pending_count);

      const registrations = Array.isArray(data.registrations) ? data.registrations : [];
      if (registrations.length) {
        afterId = Math.max(afterId, Number(data.next_id) || afterId);
        bell.dataset.afterId = String(afterId);

        const rows = registrations.map(customer => `
          <div style="display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #edf1f7;text-align:left">
            <span><strong>${escapeHtml(customer.name)}</strong><br><small>${escapeHtml(customer.email)}</small></span>
            <small style="white-space:nowrap;color:#718096">${escapeHtml(String(customer.status || '').replaceAll('_', ' '))}</small>
          </div>`).join('');

        if (window.Swal) {
          const result = await Swal.fire({
            title: registrations.length === 1 ? 'New customer registration' : `${registrations.length} new customer registrations`,
            html: `<div>${rows}</div><p style="margin:14px 0 0;color:#64748b;font-size:12px">New account requests are listed in Customer Accounts. Email verification is required before approval.</p>`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Review registrations',
            cancelButtonText: 'Close',
            confirmButtonColor: '#1682f8'
          });
          if (result.isConfirmed) window.location.assign(viewUrl);
        } else if (window.confirm(`${registrations.length} new customer registration(s). Open Customer Accounts?`)) {
          window.location.assign(viewUrl);
        }
      } else {
        afterId = Math.max(afterId, Number(data.next_id) || afterId);
        bell.dataset.afterId = String(afterId);
      }
    } catch (error) {
      // A temporary network issue should not interrupt admin work; the next poll retries.
    } finally {
      polling = false;
    }
  }

  bell.addEventListener('click', () => window.location.assign(viewUrl));
  pollRegistrations();
  window.setInterval(pollRegistrations, 10000);
})();
