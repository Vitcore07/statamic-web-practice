document.addEventListener('DOMContentLoaded', () => {
    const prevBtn = document.getElementById('prev-Btn');
    const nextBtn = document.getElementById('next-Btn');
    const items = document.querySelectorAll('.slider-card');

    let currentIndex = 0;

    function slide(index) {
        if (index < 0) {
            index = items.length - 1;
        } else if (index >= items.length) {
            index = 0;
        }

        currentIndex = index;

        items.forEach((item, i) => {
            if (i === currentIndex) {
                item.classList.remove('hidden');
                
                setTimeout(() => {
                    item.classList.remove('opacity-0');
                    item.classList.add('opacity-100');
                }, 20);
            } else {
                item.classList.remove('opacity-100');
                item.classList.add('opacity-0', 'hidden');
            }
        });
    }

    prevBtn.addEventListener('click', () => slide(currentIndex - 1));
    nextBtn.addEventListener('click', () => slide(currentIndex + 1));
    
    slide(0);
});