const scrollContainer = document.getElementById("ResExtCoreCompScrollable");

let isDragging = false;
let startX, scrollLeft;

scrollContainer.addEventListener("mousedown", (e) => {
    isDragging = true;
    scrollContainer.style.cursor = "grabbing"; // Change cursor
    startX = e.pageX - scrollContainer.offsetLeft;
    scrollLeft = scrollContainer.scrollLeft;
});

scrollContainer.addEventListener("mouseleave", () => {
    isDragging = false;
    scrollContainer.style.cursor = "grab";
});

scrollContainer.addEventListener("mouseup", () => {
    isDragging = false;
    scrollContainer.style.cursor = "grab";
});

scrollContainer.addEventListener("mousemove", (e) => {
    if (!isDragging) return;
    e.preventDefault();
    
    const x = e.pageX - scrollContainer.offsetLeft;
    const move = (x - startX);
    scrollContainer.scrollLeft = scrollLeft - move;
});

scrollContainer.addEventListener("wheel", (e) => {
    e.preventDefault();
    scrollContainer.scrollBy({
        left: e.deltaY * 4.5, // Adjust speed as needed
        behavior: "smooth"
    });
});

$(document).ready(function () {

});