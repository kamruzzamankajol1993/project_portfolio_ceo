// Small progressive enhancements for the static CEOWiki demo.
document.addEventListener('DOMContentLoaded', () => {
  // Dismissible announcement strip on the profile page.
  const announcementClose = document.querySelector('.announcement-close');
  if (announcementClose) {
    announcementClose.addEventListener('click', () => {
      const bar = announcementClose.closest('.announcement-bar');
      if (bar) { bar.remove(); document.body.classList.add('announcement-hidden'); }
    });
  }

  // Homepage category filters: keep the selected state clear.
  const filters = document.querySelectorAll('.hero-filter');
  filters.forEach((filter) => {
    filter.addEventListener('click', () => {
      filters.forEach((item) => item.classList.remove('active'));
      filter.classList.add('active');
    });
  });

  // Highlight the section navigation item currently selected.
  const sectionLinks = document.querySelectorAll('.page-nav a, .company-tabs a, .person-tabs a');
  sectionLinks.forEach((link) => {
    link.addEventListener('click', () => {
      const group = link.closest('.page-nav, .company-tabs, .person-tabs');
      if (!group) return;
      group.querySelectorAll('a').forEach((item) => item.classList.remove('active'));
      link.classList.add('active');
    });
  });

  // Header search: open the compact input only when the search icon is clicked.
  document.querySelectorAll('.header-search').forEach((search) => {
    const toggle = search.querySelector('.header-search-toggle');
    const input = search.querySelector('input');
    if (!toggle || !input) return;

    const closeSearch = () => {
      search.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Open search');
    };

    toggle.addEventListener('click', (event) => {
      event.stopPropagation();
      const isOpen = search.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? 'Close search' : 'Open search');
      if (isOpen) input.focus();
      else input.blur();
    });

    input.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeSearch();
        toggle.focus();
      }
    });

    document.addEventListener('click', (event) => {
      if (!search.contains(event.target)) closeSearch();
    });
  });

  // Featured CEOs and Companies: swipeable, auto-advancing horizontal carousels.
  const autoCarousels = document.querySelectorAll('[data-auto-carousel="true"]');

  const carouselStep = (track) => {
    const firstCard = track.firstElementChild;
    if (!firstCard) return 0;
    const styles = window.getComputedStyle(track);
    const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
    const cardStep = firstCard.getBoundingClientRect().width + gap;
    const isMobileCompanyCarousel = track.id === 'company-grid'
      && window.matchMedia('(max-width: 767.98px)').matches;
    return cardStep * (isMobileCompanyCarousel ? 2 : 1);
  };

  const moveCarousel = (track, direction = 1) => {
    if (!track) return;
    const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
    if (maxScroll < 2) return;
    const step = carouselStep(track);
    if (!step) return;

    const edgeTolerance = Math.min(8, Math.max(2, step * 0.08));
    if (direction > 0 && track.scrollLeft >= maxScroll - edgeTolerance) {
      track.scrollTo({ left: 0, behavior: 'smooth' });
    } else if (direction < 0 && track.scrollLeft <= edgeTolerance) {
      track.scrollTo({ left: maxScroll, behavior: 'smooth' });
    } else {
      track.scrollBy({ left: step * direction, behavior: 'smooth' });
    }
  };

  document.querySelectorAll('[data-carousel-next], [data-carousel-prev]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = document.getElementById(button.dataset.carouselNext || button.dataset.carouselPrev);
      moveCarousel(target, button.hasAttribute('data-carousel-next') ? 1 : -1);
    });
  });

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  autoCarousels.forEach((track) => {
    if (reducedMotion || track.children.length < 2) return;
    let paused = false;
    const timer = window.setInterval(() => {
      if (!paused && !document.hidden) moveCarousel(track, 1);
    }, 3500);

    track.addEventListener('mouseenter', () => { paused = true; });
    track.addEventListener('mouseleave', () => { paused = false; });
    track.addEventListener('focusin', () => { paused = true; });
    track.addEventListener('focusout', (event) => {
      if (!track.contains(event.relatedTarget)) paused = false;
    });
    track.addEventListener('touchstart', () => { paused = true; }, { passive: true });
    track.addEventListener('touchend', () => { paused = false; }, { passive: true });
    track.addEventListener('touchcancel', () => { paused = false; }, { passive: true });
  });

  // Demo search: allow Enter without reloading the page.
  document.querySelectorAll('.hero-search').forEach((form) => {
    form.addEventListener('submit', (event) => event.preventDefault());
  });
});
