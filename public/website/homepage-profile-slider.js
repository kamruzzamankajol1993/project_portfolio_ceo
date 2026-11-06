document.addEventListener('DOMContentLoaded', function () {
    const track = document.querySelector('[data-profile-carousel="featured-entrepreneurs"]');

    if (!track) {
        return;
    }

    const previousButton = document.querySelector('[data-profile-slider-prev="featured-entrepreneurs"]');
    const nextButton = document.querySelector('[data-profile-slider-next="featured-entrepreneurs"]');

    const getScrollDistance = function () {
        const firstCard = track.querySelector('.profile-card');

        if (!firstCard) {
            return track.clientWidth * 0.8;
        }

        const trackStyles = window.getComputedStyle(track);
        const gap = parseFloat(trackStyles.columnGap || trackStyles.gap || '0') || 0;

        return firstCard.getBoundingClientRect().width + gap;
    };

    const scrollByCards = function (direction, count) {
        track.scrollBy({
            left: getScrollDistance() * count * direction,
            behavior: 'smooth',
        });
    };

    if (previousButton) {
        previousButton.addEventListener('click', function () {
            scrollByCards(-1, 2);
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            scrollByCards(1, 2);
        });
    }

    // Auto-advance gently; pause while a visitor hovers or focuses the carousel.
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let autoAdvanceTimer = null;

    const pauseAutoAdvance = function () {
        if (autoAdvanceTimer !== null) {
            window.clearInterval(autoAdvanceTimer);
            autoAdvanceTimer = null;
        }
    };

    const startAutoAdvance = function () {
        pauseAutoAdvance();

        if (reducedMotion || track.scrollWidth <= track.clientWidth + 4) {
            return;
        }

        autoAdvanceTimer = window.setInterval(function () {
            const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;

            if (atEnd) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
                return;
            }

            scrollByCards(1, 1);
        }, 4500);
    };

    track.addEventListener('mouseenter', pauseAutoAdvance);
    track.addEventListener('mouseleave', startAutoAdvance);
    track.addEventListener('focusin', pauseAutoAdvance);
    track.addEventListener('focusout', startAutoAdvance);

    startAutoAdvance();
});
