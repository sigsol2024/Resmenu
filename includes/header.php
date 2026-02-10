<?php
/**
 * Site Header Component
 * Shared header navigation for all marketing pages
 */
?>
<header class="site-header" id="siteHeader">
    <nav class="main-nav">
        <div class="nav-container">
            <a href="/" class="logo">
                <h1>SigSol Resmenu</h1>
            </a>
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="/">Home</a></li>
                <li><a href="/restaurants-list.php">Restaurants</a></li>
                <li><a href="/templates.php">Templates</a></li>
                <li><a href="/faq.php">FAQ</a></li>
                <li><a href="/contact.php">Contact</a></li>
                <li><a href="/admin/login.php" class="btn-nav">Get Started</a></li>
            </ul>
        </div>
    </nav>
</header>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('mobileMenuToggle');
    const menu = document.getElementById('navMenu');
    const header = document.getElementById('siteHeader');
    
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('active');
            toggle.classList.toggle('active');
        });
    }
    
    // Sticky header on scroll
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            if (currentScroll > 100) {
                header.classList.add('sticky');
            } else {
                header.classList.remove('sticky');
            }
            lastScroll = currentScroll;
        });
    }
});
</script>

