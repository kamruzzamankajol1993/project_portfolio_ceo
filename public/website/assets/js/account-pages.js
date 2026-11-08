document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // Prototype-only forms may show a toast, but Laravel-backed forms must submit normally.
  document.querySelectorAll('.account-form:not([data-server-form])').forEach(form => {
    form.addEventListener('submit', event => {
      event.preventDefault();
      showAccountToast('This is a front-end preview form.');
    });
  });

  document.querySelectorAll('[data-toggle-password]').forEach(button => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.togglePassword);
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      button.innerHTML = `<i class="bi bi-${show ? 'eye-slash' : 'eye'}"></i>`;
    });
  });

  function directRows(list) {
    return [...list.children].filter(child => child.matches('[data-repeat-row], .repeat-row'));
  }

  function replaceIndexAtDepth(name, depth, newIndex) {
    let currentDepth = 0;
    return name.replace(/\[(\d+)\]/g, (match) => {
      if (currentDepth++ === depth) return `[${newIndex}]`;
      return match;
    });
  }

  function updateNestedListIds(row, rowIndex) {
    row.querySelectorAll('[data-index-depth]').forEach(nestedList => {
      const oldId = nestedList.id;
      if (!oldId) return;
      const newId = oldId.replace(/-\d+$/, `-${rowIndex}`);
      if (newId !== oldId) {
        nestedList.id = newId;
        row.querySelectorAll('[data-add-row]').forEach(button => {
          if (button.dataset.addRow === oldId) button.dataset.addRow = newId;
        });
      }
    });
  }

  function reindexList(list) {
    if (!list) return;
    const depth = Number.parseInt(list.dataset.indexDepth || '0', 10) || 0;
    directRows(list).forEach((row, rowIndex) => {
      row.querySelectorAll('[name]').forEach(field => {
        field.name = replaceIndexAtDepth(field.name, depth, rowIndex);
      });
      if (depth === 0) updateNestedListIds(row, rowIndex);
    });
  }

  function clearClonedRow(row) {
    row.querySelectorAll('input, textarea').forEach(input => {
      if (input.hasAttribute('data-clear-on-clone')) {
        input.value = '';
        return;
      }
      if (input.type === 'checkbox' || input.type === 'radio') {
        input.checked = false;
      } else if (input.type !== 'file' && input.type !== 'hidden') {
        input.value = '';
      }
    });
    row.querySelectorAll('select').forEach(select => {
      select.selectedIndex = 0;
    });
    row.querySelectorAll('[data-preview-target]').forEach(input => {
      const target = document.getElementById(input.dataset.previewTarget);
      if (target && target.dataset.defaultSrc) target.src = target.dataset.defaultSrc;
    });
  }

  // Restore consistent array indexes on load, after adding a row, and after removing one.
  [...document.querySelectorAll('.repeat-list')]
    .sort((a, b) => Number(a.dataset.indexDepth || 0) - Number(b.dataset.indexDepth || 0))
    .forEach(reindexList);

  document.addEventListener('click', event => {
    const addButton = event.target.closest('[data-add-row]');
    if (addButton) {
      const list = document.getElementById(addButton.dataset.addRow);
      const first = list ? directRows(list)[0] : null;
      if (!list || !first) return;

      const clone = first.cloneNode(true);
      clearClonedRow(clone);
      list.appendChild(clone);
      reindexList(list);
      clone.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
    }

    const removeButton = event.target.closest('[data-remove-row]');
    if (!removeButton) return;
    const row = removeButton.closest('[data-repeat-row], .repeat-row');
    const list = row?.parentElement;
    if (!row || !list) return;

    const rows = directRows(list);
    if (rows.length > 1) {
      row.remove();
    } else {
      clearClonedRow(row);
    }
    reindexList(list);
  });

  // Image previews for profile portraits, company logos and banner images.
  document.querySelectorAll('[data-preview-target]').forEach(input => {
    input.addEventListener('change', () => {
      const file = input.files?.[0];
      const target = document.getElementById(input.dataset.previewTarget);
      if (!file || !target) return;
      if (target.dataset.previewObjectUrl) URL.revokeObjectURL(target.dataset.previewObjectUrl);
      const url = URL.createObjectURL(file);
      target.dataset.previewObjectUrl = url;
      target.src = url;
    });
  });

  document.querySelectorAll('[data-preview-background]').forEach(input => {
    input.addEventListener('change', () => {
      const file = input.files?.[0];
      const target = document.getElementById(input.dataset.previewBackground);
      if (!file || !target) return;
      if (target.dataset.previewObjectUrl) URL.revokeObjectURL(target.dataset.previewObjectUrl);
      const url = URL.createObjectURL(file);
      target.dataset.previewObjectUrl = url;
      target.style.backgroundImage = `linear-gradient(110deg,rgba(4,20,45,.25),rgba(4,20,45,.08)),url("${url}")`;
      target.style.backgroundSize = 'cover';
      target.style.backgroundPosition = 'center';
    });
  });

  // Highlight the section currently in view in the long profile editor.
  const navLinks = [...document.querySelectorAll('.section-jump-nav a[href^="#"]')];
  const sections = navLinks.map(link => document.querySelector(link.getAttribute('href'))).filter(Boolean);
  if ('IntersectionObserver' in window && sections.length) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        navLinks.forEach(link => link.classList.toggle('active', link.getAttribute('href') === `#${entry.target.id}`));
      });
    }, { rootMargin: '-18% 0px -68% 0px', threshold: 0 });
    sections.forEach(section => observer.observe(section));
  }

  // Save Draft / Submit for Review buttons set the server-side action field.
  document.querySelectorAll('[data-submit-action]').forEach(button => {
    button.addEventListener('click', () => {
      const form = button.closest('form');
      const actionInput = form?.querySelector('input[name="submit_action"]');
      if (actionInput) actionInput.value = button.dataset.submitAction;
    });
  });
});

function showAccountToast(message) {
  let toast = document.getElementById('accountPrototypeToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'accountPrototypeToast';
    toast.className = 'account-prototype-toast';
    toast.setAttribute('role', 'status');
    document.body.appendChild(toast);
  }
  toast.textContent = message;
  toast.classList.add('show');
  window.clearTimeout(showAccountToast.timer);
  showAccountToast.timer = window.setTimeout(() => toast.classList.remove('show'), 3600);
}
