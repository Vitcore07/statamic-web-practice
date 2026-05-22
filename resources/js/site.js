document.addEventListener('DOMContentLoaded', () => {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const items = document.querySelectorAll('.slider-card');

    let currentIndex = 0;

    function slide(index) {
        if (index < 0) {
            index = items.length - 1;
        } else if (index >= items.length) {
            index = 0;
        }
        
        currentIndex = index;
    }

    prevBtn.addEventListener('click', () => slide(currentIndex - 1));
    nextBtn.addEventListener('click', () => slide(currentIndex + 1));
    
    slide(0);
});