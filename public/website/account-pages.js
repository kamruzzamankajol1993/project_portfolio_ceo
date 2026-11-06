// Customer account interactions. Profile data is submitted to Laravel; no browser-only draft storage is used.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.announcement-close').forEach(button => button.addEventListener('click', () => button.closest('.announcement-wrap')?.classList.add('d-none')));
  document.querySelectorAll('form.nav-search').forEach(form => form.addEventListener('submit', event => event.preventDefault()));

  const mobileNav = document.getElementById('mobileNav');
  document.querySelectorAll('#mobileNav a').forEach(link => link.addEventListener('click', () => {
    if (window.bootstrap && mobileNav) window.bootstrap.Offcanvas.getOrCreateInstance(mobileNav).hide();
  }));

  document.querySelectorAll('[data-toggle-password]').forEach(button => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-pressed', String(show));
      button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      const icon = button.querySelector('i');
      if (icon) icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });

  document.querySelectorAll('[data-image-preview]').forEach(input => {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      const target = document.getElementById(input.dataset.imagePreview);
      if (!file || !target || !file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = () => {
        if (target.classList.contains('company-banner-upload')) {
          target.style.backgroundImage = `linear-gradient(rgba(8,43,104,.12),rgba(8,43,104,.12)),url("${reader.result}")`;
          const overlay = target.querySelector('.banner-upload-overlay');
          if (overlay) overlay.style.background = 'rgba(255,255,255,.88)';
        } else {
          target.innerHTML = `<img src="${reader.result}" alt="Selected image preview">`;
        }
      };
      reader.readAsDataURL(file);
    });
  });

  let dynamicIndex = Date.now();
  const nextIndex = () => dynamicIndex++;
  const cloneTemplate = (templateId, replacements = {}) => {
    const template = document.getElementById(templateId);
    if (!template) return null;
    let html = template.innerHTML;
    Object.entries(replacements).forEach(([key, value]) => {
      html = html.replaceAll(`__${key}__`, String(value));
    });
    const holder = document.createElement('div');
    holder.innerHTML = html.trim();
    return holder.firstElementChild;
  };

  document.addEventListener('click', event => {
    const addRowButton = event.target.closest('[data-add-row]');
    if (addRowButton) {
      const container = document.getElementById(addRowButton.dataset.addRow);
      const row = cloneTemplate(addRowButton.dataset.template, { INDEX: nextIndex() });
      if (container && row) container.appendChild(row);
      return;
    }

    if (event.target.closest('[data-remove-row]')) {
      event.target.closest('[data-repeat-row]')?.remove();
      return;
    }

    if (event.target.closest('[data-add-section]')) {
      const container = document.getElementById('profile-sections-container');
      const sectionIndex = nextIndex();
      const section = cloneTemplate('profile-section-template', { SECTION: sectionIndex });
      if (container && section) {
        section.dataset.sectionIndex = sectionIndex;
        container.appendChild(section);
      }
      return;
    }

    if (event.target.closest('[data-remove-section]')) {
      event.target.closest('[data-section-row]')?.remove();
      return;
    }

    if (event.target.closest('[data-add-item]')) {
      const section = event.target.closest('[data-section-row]');
      const container = section?.querySelector('[data-items-container]');
      if (!section || !container) return;
      const firstName = section.querySelector('[name^="sections["]')?.name || '';
      const match = firstName.match(/^sections\[(\d+)\]/);
      const sectionIndex = match ? match[1] : nextIndex();
      const itemIndexes = Array.from(container.querySelectorAll('[name*="[items]["]'))
        .map(input => Number((input.name.match(/\[items\]\[(\d+)\]/) || [])[1] || 0));
      const itemIndex = itemIndexes.length ? Math.max(...itemIndexes) + 1 : 0;
      const item = cloneTemplate('profile-section-item-template', { SECTION: sectionIndex, ITEM: itemIndex });
      if (item) container.appendChild(item);
    }
  });
});
