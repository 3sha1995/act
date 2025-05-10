/* homepage.js */

document.addEventListener("DOMContentLoaded", function () {
    let video = document.getElementById("delayedVideo");
    let container = document.querySelector(".homepage-video-container");

    setTimeout(() => {
        video.play();
        video.classList.add("playing"); // Make video visible
        container.classList.add("video-playing"); // Remove gradient
    }, 1000);
});

document.addEventListener('DOMContentLoaded', function() {
    const newsGrid = document.querySelector('.news-grid');
    const newsItems = document.querySelectorAll('.news-item');
    const dots = document.querySelectorAll('.carousel-dot');

    // Set first item as active
    newsItems[0].classList.add('active');

    // Handle scroll
    newsGrid.addEventListener('scroll', () => {
        const scrollPosition = newsGrid.scrollLeft;
        const itemWidth = newsItems[0].offsetWidth;
        
        // Calculate which item is in center
        const activeIndex = Math.round(scrollPosition / itemWidth);
        
        // Update active classes
        newsItems.forEach(item => item.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        if (newsItems[activeIndex]) {
            newsItems[activeIndex].classList.add('active');
            dots[activeIndex].classList.add('active');
        }
    });

    // Handle dot clicks
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            const itemWidth = newsItems[0].offsetWidth;
            newsGrid.scrollTo({
                left: itemWidth * index,
                behavior: 'smooth'
            });
        });
    });
});