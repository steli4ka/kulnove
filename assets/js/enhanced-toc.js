document.addEventListener('DOMContentLoaded', function() {
    "use strict";

    class EnhancedTOC {
        constructor() {
            console.log('EnhancedTOC initialized');

            this.content = document.querySelector('.entry-content');
            this.toc = document.querySelector('.toc-content');

            console.log('Content element:', this.content);
            console.log('TOC element:', this.toc);

            if (!this.content || !this.toc) {
                console.warn('Required elements not found');
                return;
            }

            this.config = {
                headingSelectors: 'h2, h3, h4, h5, h6',
                headerOffset: 100,
                animationDuration: 300,
                smoothScrollBehavior: 'ease-in-out',
                intersectionThreshold: [0, 0.25, 0.5, 0.75, 1],
                debounceDuration: 100
            };

            this.progressBar = null;
            this.readingProgress = 0;
            this.activeHeading = null;
            this.headings = [];
            this.tocItems = new Map();

            this.init();
        }

        init() {
            this.setupProgressBar();
            this.generateTOC();
            this.setupScrollHandlers();
            this.setupResizeHandler();
            this.initializeAnimations();
            this.setupMobileView();
            window.addEventListener('resize', this.debounce(() => this.handleResize(), 150));
        }

        setupProgressBar() {
            const progressContainer = document.createElement('div');
            progressContainer.className = 'toc-progress-container';

            this.progressBar = document.createElement('div');
            this.progressBar.className = 'toc-progress-bar';

            progressContainer.appendChild(this.progressBar);
            this.toc.parentElement.insertBefore(progressContainer, this.toc);
        }

        generateTOC() {
            const headings = this.content.querySelectorAll(this.config.headingSelectors);
            console.log('Found headings:', headings.length);

            if (headings.length === 0) {
                this.toc.innerHTML = '<p class="toc-empty">Няма налично съдържание</p>';
                return;
            }

            const tocList = document.createElement('ul');
            tocList.className = 'toc-list';
            let currentLevel = 1;
            let currentList = tocList;
            let listStack = [tocList];
            let counter = [0, 0, 0, 0, 0, 0];

            headings.forEach((heading, index) => {
                const headingId = heading.id || `section-${index}`;
                heading.id = headingId;

                const level = parseInt(heading.tagName.charAt(1));
                counter[level - 1]++;
                for (let i = level; i < counter.length; i++) {
                    counter[i] = 0;
                }

                const numberLabel = counter.slice(0, level).filter(n => n !== 0).join('.');
                const listItem = this.createTOCItem(heading, numberLabel, level);

                if (level > currentLevel) {
                    const parentItem = listStack[listStack.length - 1].lastElementChild;
                    if (parentItem) {
                        const newList = document.createElement('ul');
                        newList.className = 'toc-sublist';
                        parentItem.appendChild(newList);
                        listStack.push(newList);
                        currentList = newList;
                    } else {
                        // Ако няма parentItem, добавяме към текущия списък
                        currentList.appendChild(listItem);
                    }
                } else if (level < currentLevel) {
                    while (level < currentLevel && listStack.length > 1) {
                        listStack.pop();
                        currentLevel--;
                    }
                    currentList = listStack[listStack.length - 1];
                    currentList.appendChild(listItem);
                } else {
                    currentList.appendChild(listItem);
                }

                currentLevel = level;
                this.tocItems.set(headingId, listItem);
                this.headings.push({ id: headingId, element: heading, level });
            });

            this.toc.appendChild(tocList);
        }
        
        createTOCItem(heading, numberLabel, level) {
            const listItem = document.createElement('li');
            listItem.className = `toc-item toc-level-${level}`;

            const link = document.createElement('a');
            link.href = `#${heading.id}`;
            link.className = 'toc-link';

            const numberSpan = document.createElement('span');
            numberSpan.className = 'toc-number';
            numberSpan.textContent = numberLabel;

            const textSpan = document.createElement('span');
            textSpan.className = 'toc-text';
            textSpan.textContent = heading.textContent;

            link.appendChild(numberSpan);
            link.appendChild(textSpan);

            const progress = document.createElement('div');
            progress.className = 'toc-item-progress';

            listItem.appendChild(link);
            listItem.appendChild(progress);

            this.addClickHandler(link, heading);

            return listItem;
        }

        addClickHandler(link, heading) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.scrollToHeading(heading);
            });
        }

        scrollToHeading(heading) {
            const offset = this.config.headerOffset;
            const targetPosition = heading.getBoundingClientRect().top + window.pageYOffset - offset;

            const startPosition = window.pageYOffset;
            const distance = targetPosition - startPosition;
            const startTime = performance.now();

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / this.config.animationDuration, 1);

                const easeInOutQuad = t => t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;

                window.scrollTo(0, startPosition + distance * easeInOutQuad(progress));

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            requestAnimationFrame(animate);
        }

        setupScrollHandlers() {
            const observer = new IntersectionObserver(
                (entries) => this.handleIntersection(entries),
                {
                    rootMargin: `-${this.config.headerOffset}px 0px -66% 0px`,
                    threshold: this.config.intersectionThreshold
                }
            );

            this.headings.forEach(({ element }) => observer.observe(element));

            this.updateReadingProgress = this.debounce(() => {
                const windowHeight = window.innerHeight;
                const documentHeight = document.documentElement.scrollHeight - windowHeight;
                const scrolled = window.pageYOffset;
                this.readingProgress = (scrolled / documentHeight) * 100;
                this.updateProgress();
            }, this.config.debounceDuration);

            window.addEventListener('scroll', () => this.updateReadingProgress());
        }

        handleIntersection(entries) {
            entries.forEach(entry => {
                const listItem = this.tocItems.get(entry.target.id);
                if (!listItem) return;

                if (entry.isIntersecting) {
                    this.setActiveItem(listItem);
                    this.updateItemProgress(entry.target.id, entry.intersectionRatio);
                }
            });
        }

        setActiveItem(listItem) {
            this.tocItems.forEach(item => item.classList.remove('active'));
            listItem.classList.add('active');

            const tocContainer = this.toc;
            const itemRect = listItem.getBoundingClientRect();
            const containerRect = tocContainer.getBoundingClientRect();

            if (itemRect.bottom > containerRect.bottom) {
                tocContainer.scrollTop += itemRect.bottom - containerRect.bottom;
            } else if (itemRect.top < containerRect.top) {
                tocContainer.scrollTop -= containerRect.top - itemRect.top;
            }
        }

        updateItemProgress(headingId, ratio) {
            const listItem = this.tocItems.get(headingId);
            if (listItem) {
                const progress = listItem.querySelector('.toc-item-progress');
                progress.style.transform = `scaleX(${ratio})`;
            }
        }

        updateProgress() {
            if (this.progressBar) {
                this.progressBar.style.transform = `scaleX(${this.readingProgress / 100})`;
            }
        }

        setupResizeHandler() {
            const resizeObserver = new ResizeObserver(this.debounce(() => {
                this.updateReadingProgress();
            }, 150));

            resizeObserver.observe(document.body);
        }

        setupMobileView() {
            if (window.innerWidth <= 768) {
                const tocInner = this.toc.parentElement;

                if (!tocInner.querySelector('.toc-toggle')) {
                    const toggleButton = document.createElement('button');
                    toggleButton.className = 'toc-toggle';
                    toggleButton.innerHTML = 'Съдържание';

                    tocInner.insertBefore(toggleButton, this.toc);

                    toggleButton.addEventListener('click', () => {
                        const isExpanded = tocInner.classList.toggle('expanded');
                        const maxHeight = isExpanded ? this.toc.scrollHeight + 'px' : '0';
                        this.toc.style.maxHeight = maxHeight;

                        if (isExpanded) {
                            const yOffset = -100;
                            const y = tocInner.getBoundingClientRect().top + window.pageYOffset + yOffset;
                            window.scrollTo({ top: y, behavior: 'smooth' });
                        }
                    });

                    this.toc.addEventListener('click', (e) => {
                        if (e.target.closest('.toc-link')) {
                            tocInner.classList.remove('expanded');
                            this.toc.style.maxHeight = '0';
                        }
                    });
                }
            }
        }

        handleResize() {
            const isMobile = window.innerWidth <= 768;
            const tocInner = this.toc.parentElement;

            if (isMobile) {
                if (!tocInner.querySelector('.toc-toggle')) {
                    this.setupMobileView();
                }
            } else {
                const toggle = tocInner.querySelector('.toc-toggle');
                if (toggle) {
                    toggle.remove();
                    tocInner.classList.remove('expanded');
                    this.toc.style.maxHeight = '';
                }
            }
        }

        initializeAnimations() {
            this.toc.style.opacity = '0';
            requestAnimationFrame(() => {
                this.toc.style.transition = 'opacity 0.3s ease-in-out';
                this.toc.style.opacity = '1';
            });
        }

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    }

    // Създаваме инстанция само ако сме на страница с публикация
    if (document.body.classList.contains('single-post') || document.body.classList.contains('single')) {
        new EnhancedTOC();
    }
});
