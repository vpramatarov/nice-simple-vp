document.addEventListener('DOMContentLoaded', function() {
    const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

    if (!isTouchDevice) {
        return;
    }

    let touchStartX = 0;
    let touchEndX = 0;
    const sliderContainer = document.querySelector('.hero-slider');
    const radios = document.querySelectorAll('input[name="slider"]');
    const minSwipeDistance = 50;

    sliderContainer.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, {passive: true});

    sliderContainer.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleGesture();
    }, {passive: true});

    function handleGesture() {
        let difference = touchEndX - touchStartX;

        // swipe left -> next slide
        if (difference < -minSwipeDistance) {
            changeSlide('next');
        }
        // swipe right - previous slide
        if (difference > minSwipeDistance) {
            changeSlide('prev');
        }
    }

    function changeSlide(direction) {
        let currentIndex = Array.from(radios).findIndex(r => r.checked);
        let nextIndex = currentIndex;

        if (direction === 'next') {
            nextIndex = (currentIndex + 1) % radios.length;
        } else if (direction === 'prev') {
            nextIndex = (currentIndex - 1 + radios.length) % radios.length;
        }

        radios[nextIndex].checked = true;
    }
});