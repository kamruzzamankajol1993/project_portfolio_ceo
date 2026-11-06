document.addEventListener('DOMContentLoaded', () => {
  const directory = document.querySelector('[data-profile-directory]');
  if (!directory) return;

  const list = directory.querySelector('[data-profile-list]');
  const sentinel = directory.querySelector('[data-profile-sentinel]');
  const button = directory.querySelector('[data-profile-load-more]');
  const status = directory.querySelector('[data-profile-status]');
  let nextPage = directory.dataset.nextPage || null;
  let loading = false;

  const loadNextPage = async () => {
    if (!nextPage || loading) return;
    loading = true;
    if (button) button.disabled = true;
    if (status) status.textContent = 'Loading more profiles...';

    try {
      const response = await fetch(nextPage, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin'
      });
      if (!response.ok) throw new Error('Could not load more profiles.');
      const payload = await response.json();
      if (payload.html && list) list.insertAdjacentHTML('beforeend', payload.html);
      nextPage = payload.next_page_url || null;
      if (status) status.textContent = nextPage ? 'Scroll to load more profiles.' : 'You are viewing all published profiles.';
      if (button) button.hidden = !nextPage;
    } catch (error) {
      if (status) status.textContent = error.message || 'Could not load more profiles. Please try again.';
      if (button) button.hidden = false;
    } finally {
      loading = false;
      if (button) button.disabled = false;
    }
  };

  button?.addEventListener('click', loadNextPage);
  if ('IntersectionObserver' in window && sentinel) {
    const observer = new IntersectionObserver(entries => {
      if (entries.some(entry => entry.isIntersecting)) loadNextPage();
    }, { rootMargin: '320px 0px' });
    observer.observe(sentinel);
  }
});
