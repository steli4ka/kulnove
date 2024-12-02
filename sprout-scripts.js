document.addEventListener('DOMContentLoaded', function() {
    // Функционалност за табовете
    function initTabs() {
        const tabs = document.querySelectorAll('.sprouts-tab-button');
        const contents = document.querySelectorAll('.sprouts-tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;

                // Деактивиране на всички табове
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => {
                    c.classList.remove('active');
                    c.style.display = 'none';
                });

                // Активиране на избрания таб
                tab.classList.add('active');
                const activeContent = document.getElementById(target);
                activeContent.style.display = 'block';

                // Малко закъснение за анимацията
                setTimeout(() => {
                    activeContent.classList.add('active');
                }, 10);
            });
        });
    }

    // Плавно скролване към секциите
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    // Инициализация
    initTabs();
    initSmoothScroll();
});
