(function () {
  'use strict';

  function escapeText(value) {
    return String(value ?? '');
  }

  function renderMessage(container, message, state) {
    container.replaceChildren();
    const item = document.createElement('div');
    item.className = 'search-result-message' + (state ? ' is-' + state : '');
    item.textContent = message;
    container.appendChild(item);
    container.hidden = false;
  }

  function renderResults(container, results, query) {
    container.replaceChildren();

    if (!Array.isArray(results) || results.length === 0) {
      renderMessage(container, 'No published profiles found for “' + query + '”. Try another name or industry.', 'empty');
      return;
    }

    const heading = document.createElement('div');
    heading.className = 'search-results-heading';
    heading.textContent = results.length + (results.length === 1 ? ' result' : ' results') + ' found';
    container.appendChild(heading);

    results.forEach(function (result) {
      const link = document.createElement('a');
      link.className = 'search-result-item';
      link.href = escapeText(result.url || '#');
      link.setAttribute('role', 'option');

      const type = document.createElement('span');
      type.className = 'search-result-type';
      type.textContent = escapeText(result.type || 'Profile');

      const title = document.createElement('strong');
      title.className = 'search-result-title';
      title.textContent = escapeText(result.title || 'Untitled profile');

      link.append(type, title);
      if (result.subtitle) {
        const subtitle = document.createElement('span');
        subtitle.className = 'search-result-subtitle';
        subtitle.textContent = escapeText(result.subtitle);
        link.appendChild(subtitle);
      }
      if (result.description) {
        const description = document.createElement('span');
        description.className = 'search-result-description';
        description.textContent = escapeText(result.description);
        link.appendChild(description);
      }
      container.appendChild(link);
    });

    container.hidden = false;
  }

  function initialiseSearch(form) {
    const input = form.querySelector('input[type="search"]');
    const container = document.getElementById(form.dataset.resultsId || '');
    const endpoint = form.dataset.searchUrl;
    const wrapper = form.closest('.site-search-box');

    if (!input || !container || !endpoint || form.dataset.ajaxSearchReady === '1') return;
    form.dataset.ajaxSearchReady = '1';

    let debounceTimer = null;
    let activeRequest = null;
    let lastQuery = '';

    function hideResults() {
      container.hidden = true;
      input.setAttribute('aria-expanded', 'false');
    }

    async function search() {
      const query = input.value.trim();
      if (query.length < 2) {
        hideResults();
        return;
      }
      if (query === lastQuery && !container.hidden) return;
      lastQuery = query;

      if (activeRequest) activeRequest.abort();
      activeRequest = new AbortController();
      renderMessage(container, 'Searching published profiles…', 'loading');
      input.setAttribute('aria-expanded', 'true');

      try {
        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('q', query);
        const response = await fetch(url.toString(), {
          method: 'GET',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          signal: activeRequest.signal
        });

        if (!response.ok) throw new Error('Search request failed');
        const payload = await response.json();
        if (input.value.trim() !== query) return;
        renderResults(container, payload.results || [], query);
        input.setAttribute('aria-expanded', 'true');
      } catch (error) {
        if (error.name === 'AbortError') return;
        renderMessage(container, 'Search is temporarily unavailable. Please try again.', 'error');
        input.setAttribute('aria-expanded', 'true');
      }
    }

    input.addEventListener('input', function () {
      lastQuery = '';
      window.clearTimeout(debounceTimer);
      const query = input.value.trim();
      if (query.length < 2) {
        if (activeRequest) activeRequest.abort();
        hideResults();
        return;
      }
      debounceTimer = window.setTimeout(search, 280);
    });

    input.addEventListener('focus', function () {
      if (input.value.trim().length >= 2 && container.hidden) search();
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') hideResults();
    });

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      window.clearTimeout(debounceTimer);
      lastQuery = '';
      search();
    });

    document.addEventListener('click', function (event) {
      if (wrapper && !wrapper.contains(event.target)) hideResults();
    });
  }

  document.querySelectorAll('[data-ajax-search]').forEach(initialiseSearch);

  document.querySelectorAll('[data-popular-search]').forEach(function (button) {
    button.addEventListener('click', function () {
      const hero = button.closest('.hero-copy');
      const input = hero ? hero.querySelector('[data-ajax-search] input[type="search"]') : null;
      if (!input) return;
      input.value = button.dataset.popularSearch || button.textContent.trim();
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.focus();
    });
  });
})();
