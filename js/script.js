const genresContainer = document.querySelector('.genres-container');

let scrollInterval = setInterval(() => {
    genresContainer.scrollBy({
        left: genresContainer.offsetWidth,
        behavior: 'smooth'
    });
}, 3000);

genresContainer.addEventListener('mouseover', () => {
    clearInterval(scrollInterval);
});

genresContainer.addEventListener('mouseout', () => {
    scrollInterval = setInterval(() => {
        genresContainer.scrollBy({
            left: genresContainer.offsetWidth,
            behavior: 'smooth'
        });
    }, 3000);
});
