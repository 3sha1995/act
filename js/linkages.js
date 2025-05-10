document.addEventListener("DOMContentLoaded", function () {
    const carousel = document.querySelector(".Linkages-HoverableImage");
    const images = carousel.children;
    const cloneCount = images.length;

    // Clone images for seamless looping
    for (let i = 0; i < cloneCount; i++) {
        let clone = images[i].cloneNode(true);
        carousel.appendChild(clone);
    }
});