/**
 * Public Menu Page JavaScript
 * Search and filter functionality
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        const searchBox = document.getElementById('searchBox');
        const filterButtons = document.querySelectorAll('.category-filter-btn');
        const menuItems = document.querySelectorAll('.menu-item');
        const categorySections = document.querySelectorAll('.category-section');
        let activeCategory = 'all';
        
        if (!searchBox || filterButtons.length === 0) {
            return; // Elements not found, exit
        }
        
        // Search functionality
        searchBox.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterMenuItems(searchTerm, activeCategory);
        });
        
        // Category filter functionality
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                activeCategory = this.getAttribute('data-category');
                const searchTerm = searchBox.value.toLowerCase().trim();
                filterMenuItems(searchTerm, activeCategory);
                
                // Scroll to top of menu
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
        
        function filterMenuItems(searchTerm, category) {
            let hasVisibleItems = false;
            
            categorySections.forEach(section => {
                const sectionSlug = section.getAttribute('data-category-slug');
                const sectionItems = section.querySelectorAll('.menu-item');
                let sectionHasVisible = false;
                
                sectionItems.forEach(item => {
                    const itemName = item.getAttribute('data-item-name') || '';
                    const itemDescription = item.getAttribute('data-item-description') || '';
                    const itemCategory = item.getAttribute('data-category-slug') || '';
                    
                    const matchesSearch = !searchTerm || 
                        itemName.includes(searchTerm) || 
                        itemDescription.includes(searchTerm);
                    
                    const matchesCategory = category === 'all' || itemCategory === category;
                    
                    if (matchesSearch && matchesCategory) {
                        item.style.display = '';
                        item.setAttribute('data-hidden', 'false');
                        sectionHasVisible = true;
                        hasVisibleItems = true;
                    } else {
                        item.style.display = 'none';
                        item.setAttribute('data-hidden', 'true');
                    }
                });
                
                // Show/hide category section based on visible items
                if (sectionHasVisible) {
                    section.style.display = '';
                    section.setAttribute('data-hidden', 'false');
                } else {
                    section.style.display = 'none';
                    section.setAttribute('data-hidden', 'true');
                }
            });
            
            // Show no results message if needed
            let noResultsMsg = document.querySelector('.no-results');
            if (!hasVisibleItems) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results';
                    noResultsMsg.textContent = 'No menu items found matching your search.';
                    const main = document.querySelector('main');
                    if (main) {
                        main.appendChild(noResultsMsg);
                    }
                }
            } else {
                if (noResultsMsg) {
                    noResultsMsg.remove();
                }
            }
        }
        
        // Smooth scroll for category links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    }
})();

