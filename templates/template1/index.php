<?php
/**
 * Template 1: Spinhive Design
 * Modern restaurant menu template with alternating layout sections
 */

// Note: $restaurant, $categories, $customization, and $headerMenuItems are already provided by the template loader

// Parse header menu items
$navLinks = [];
if (!empty($headerMenuItems)) {
    if (is_string($headerMenuItems)) {
        $decoded = json_decode($headerMenuItems, true);
        if (is_array($decoded)) {
            $navLinks = $decoded;
        }
    } elseif (is_array($headerMenuItems)) {
        $navLinks = $headerMenuItems;
    }
}

// Count active categories with menu items
$activeCategoryCount = 0;
if (!empty($categories) && is_array($categories)) {
    foreach ($categories as $category) {
        if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']) {
            $activeCategoryCount++;
        }
    }
}

// Use toggle menu if more than 5 categories
$useToggleMenu = $activeCategoryCount > 5;

// Get the correct base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$currentDir = ($currentDir === '/' || $currentDir === '\\') ? '' : rtrim($currentDir, '/');
$baseUrl = $protocol . $host . $currentDir;
$uploadBaseUrl = $baseUrl . '/uploads';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?php echo htmlspecialchars($restaurant['name']); ?></title>
<link rel="stylesheet" href="<?php echo $baseUrl . '/templates/template1/style.css'; ?>">
</head>

<body>

<!-- Header -->
<header class="header">
  <div class="container">
    <nav class="nav">
      <div class="logo">
        <?php if (!empty($restaurant['logo']) && empty($isTemplatePreview)): ?>
          <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" style="max-height: 40px; width: auto;">
        <?php elseif (!empty($isTemplatePreview)): ?>
          Logo
        <?php else: ?>
          <?php echo htmlspecialchars($restaurant['name']); ?>
        <?php endif; ?>
      </div>
      <?php if ($useToggleMenu): ?>
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
          <span></span>
          <span></span>
          <span></span>
        </button>
      <?php else: ?>
        <div class="nav-links" id="navLinks">
          <?php 
          // Show active categories as navigation links
          if (!empty($categories) && is_array($categories)):
            foreach ($categories as $category): 
              if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']):
          ?>
            <a href="#<?php echo htmlspecialchars($category['slug']); ?>-section" class="category-nav-link"><?php echo htmlspecialchars($category['name']); ?></a>
          <?php 
              endif;
            endforeach;
          endif;
          ?>
          <button class="btn btn-primary" onclick="scrollToFirstMenu()">View Menu</button>
        </div>
      <?php endif; ?>
    </nav>
  </div>
</header>

<!-- Sidebar for category menu (when toggle is used) -->
<?php if ($useToggleMenu): ?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="category-sidebar" id="categorySidebar">
  <div class="sidebar-content">
    <div class="sidebar-header">
      <h3>Menu Categories</h3>
      <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">
        <span></span>
        <span></span>
      </button>
    </div>
    <nav class="sidebar-nav">
      <?php 
      if (!empty($categories) && is_array($categories)):
        foreach ($categories as $category): 
          if (!empty($category['menu_items']) && is_array($category['menu_items']) && $category['is_active']):
      ?>
        <a href="#<?php echo htmlspecialchars($category['slug']); ?>-section" class="sidebar-nav-link"><?php echo htmlspecialchars($category['name']); ?></a>
      <?php 
          endif;
        endforeach;
      endif;
      ?>
    </nav>
  </div>
</div>
<?php endif; ?>

<script>
// Menu toggle and sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const navLinks = document.getElementById('navLinks');
  const categorySidebar = document.getElementById('categorySidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const sidebarClose = document.getElementById('sidebarClose');
  
  // Handle toggle menu (for > 5 categories)
  if (mobileMenuToggle && categorySidebar) {
    function openSidebar() {
      categorySidebar.classList.add('active');
      if (sidebarOverlay) {
        sidebarOverlay.classList.add('active');
      }
      document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
      categorySidebar.classList.remove('active');
      if (sidebarOverlay) {
        sidebarOverlay.classList.remove('active');
      }
      document.body.style.overflow = '';
    }
    
    mobileMenuToggle.addEventListener('click', function() {
      openSidebar();
    });
    
    if (sidebarOverlay) {
      sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    if (sidebarClose) {
      sidebarClose.addEventListener('click', closeSidebar);
    }
    
    // Close sidebar on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && categorySidebar.classList.contains('active')) {
        closeSidebar();
      }
    });
    
    // Close sidebar when clicking on a category link
    const sidebarLinks = categorySidebar.querySelectorAll('.sidebar-nav-link');
    sidebarLinks.forEach(function(link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        closeSidebar();
        // Scroll to section
        setTimeout(function() {
          const targetSection = document.querySelector(targetId);
          if (targetSection) {
            targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }, 300); // Wait for sidebar to close
      });
    });
  }
  
  // Handle inline nav links (for <= 5 categories)
  if (navLinks) {
    const navLinksItems = navLinks.querySelectorAll('a.category-nav-link');
    navLinksItems.forEach(function(link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        // Scroll to section
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
          targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  }
  
  // Function to scroll to first menu section
  function scrollToFirstMenu() {
    const firstSection = document.querySelector('.menu-section');
    if (firstSection) {
      firstSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
  
  // Make scrollToFirstMenu available globally
  window.scrollToFirstMenu = scrollToFirstMenu;
  
  // Adjust container height to accommodate card content (desktop only)
  function adjustContainerHeights() {
    if (window.innerWidth <= 1024) return; // Skip on mobile
    
    const menuContainers = document.querySelectorAll('.menu-container');
    menuContainers.forEach(function(container) {
      const card = container.querySelector('.menu-card');
      const image = container.querySelector('.menu-image');
      
      if (card && image) {
        // On desktop, ensure container is tall enough for card
        const cardHeight = card.offsetHeight;
        const imageHeight = image.offsetHeight;
        const minHeight = Math.max(imageHeight, cardHeight + 120); // Card height + padding
        container.style.minHeight = minHeight + 'px';
      }
    });
  }
  
  // Run on load and resize (debounced)
  adjustContainerHeights();
  let resizeTimeout;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(adjustContainerHeights, 100);
  });
  
  // Viewport-triggered animation using Intersection Observer (much faster than scroll events)
  const animationObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('animate-in');
        // Stop observing once animated
        animationObserver.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.1, // Trigger when 10% of element is visible
    rootMargin: '0px 0px -50px 0px' // Trigger slightly before element fully enters viewport
  });
  
  // Observe all images and cards
  const images = document.querySelectorAll('.menu-image, .visit-image');
  const cards = document.querySelectorAll('.menu-card, .visit-card-content');
  
  images.forEach(function(image) {
    animationObserver.observe(image);
  });
  
  cards.forEach(function(card) {
    animationObserver.observe(card);
  });
});
</script>

<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-left">
        <?php 
        $rating = floatval($restaurant['google_rating'] ?? 4.5);
        $ratingSource = htmlspecialchars($restaurant['rating_source'] ?? 'Google');
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
        $starsDisplay = str_repeat('★', $fullStars) . ($hasHalfStar ? '½' : '') . str_repeat('☆', $emptyStars);
        ?>
        <div class="rating-badge">
          <span><?php echo $ratingSource; ?></span>
          <span class="stars"><?php echo str_repeat('★', $fullStars) . ($hasHalfStar ? '½' : '') . str_repeat('☆', $emptyStars); ?></span>
          <span>(<?php echo number_format($rating, 1); ?>)</span>
        </div>
        <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
        <p class="hero-text"><?php echo htmlspecialchars($restaurant['description'] ?? 'Welcome to our restaurant, where every experience is a step closer to happiness.'); ?></p>
        <div class="hero-buttons">
          <button class="btn btn-primary" onclick="scrollToFirstMenu()">View Menu</button>
        </div>
      </div>
      
      <div class="hero-image-container">
        <?php if (!empty($restaurant['hero_image_url'])): ?>
          <div class="hero-main-image" style="background-image: url('<?php echo htmlspecialchars($restaurant['hero_image_url']); ?>'); background-size: cover; background-position: center;"></div>
        <?php elseif (!empty($restaurant['hero_image'])): ?>
          <div class="hero-main-image" style="background-image: url('<?php echo $uploadBaseUrl . '/heroes/' . htmlspecialchars($restaurant['hero_image']); ?>'); background-size: cover; background-position: center;"></div>
        <?php elseif (!empty($restaurant['logo'])): ?>
          <div class="hero-main-image" style="background-image: url('<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>'); background-size: cover; background-position: center;"></div>
        <?php else: ?>
          <div class="hero-main-image"></div>
        <?php endif; ?>
        <div class="hero-overlay-card">
          <div class="overlay-title"><?php echo htmlspecialchars($restaurant['name']); ?></div>
          <div class="overlay-stars">★★★★★</div>
          <div>Quick & Reliable</div>
          <button class="btn btn-primary overlay-btn" onclick="scrollToFirstMenu()">View Menu</button>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
// Loop through categories with alternating layout
$isAlternate = false;
if (!empty($categories) && is_array($categories)):
foreach ($categories as $category): 
    if (!empty($category['menu_items']) && is_array($category['menu_items'])): 
        $isAlternate = !$isAlternate;
?>
<!-- SECTION: <?php echo htmlspecialchars($category['name']); ?> -->
<section class="menu-section <?php echo $isAlternate ? 'alternate' : ''; ?>" id="<?php echo htmlspecialchars($category['slug']); ?>-section">
  <div class="container">
    <div class="menu-container">
      <?php if ($category['image']): ?>
        <div class="menu-image" style="background-image: url('<?php echo $uploadBaseUrl . '/categories/' . htmlspecialchars($category['image']); ?>'); background-size: cover; background-position: center;"></div>
      <?php else: ?>
        <div class="menu-image"></div>
      <?php endif; ?>
      <div class="menu-card">
        <div class="category-title"><?php echo htmlspecialchars($category['name']); ?></div>
        <div class="menu-items">
          <?php foreach ($category['menu_items'] as $item): ?>
            <div class="menu-item">
              <div class="menu-item-content">
                <?php if (!empty($item['image'])): ?>
                  <div class="item-image">
                    <img src="<?php echo $uploadBaseUrl . '/menu-items/' . htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                  </div>
                <?php endif; ?>
                <div class="item-details">
                  <div class="item-name">
                    <?php echo htmlspecialchars($item['name']); ?>
                    <?php if (!$item['is_available']): ?>
                      <span class="unavailable-badge">Unavailable</span>
                    <?php endif; ?>
                  </div>
                  <div class="item-price"><?php echo 'N' . number_format($item['price'], 0, '.', ','); ?></div>
                  <?php if ($item['description']): ?>
                    <div class="item-description"><?php echo htmlspecialchars($item['description']); ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        
        <div class="order-btn-container">
          <button class="btn btn-primary" onclick="scrollToFirstMenu()">View Menu</button>
        </div>
      </div>
    </div>
  </div>
</section>

<?php 
    endif;
endforeach;
endif; 
?>

<!-- Visit Section -->
<section class="visit-section" id="visit-section">
  <div class="container">
    <div class="visit-container">
      <?php if ($restaurant['map_latitude'] && $restaurant['map_longitude']): ?>
        <div class="visit-image">
          <iframe 
            width="100%" 
            height="100%" 
            style="border:0; border-radius: var(--radius-xl);" 
            loading="lazy" 
            allowfullscreen
            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dS6fa4U5iKJ&q=<?php echo htmlspecialchars($restaurant['map_latitude']); ?>,<?php echo htmlspecialchars($restaurant['map_longitude']); ?>">
          </iframe>
        </div>
      <?php else: ?>
        <div class="visit-image"></div>
      <?php endif; ?>
      <div class="visit-card-content">
        <h2>Visit Us or Place an Order</h2>
        <?php if ($restaurant['map_latitude'] && $restaurant['map_longitude']): ?>
          <p class="visit-text"><?php echo htmlspecialchars($restaurant['map_latitude']); ?>, <?php echo htmlspecialchars($restaurant['map_longitude']); ?></p>
        <?php endif; ?>
        <?php if ($restaurant['address']): ?>
          <p class="visit-text"><?php echo htmlspecialchars($restaurant['address']); ?></p>
        <?php endif; ?>
        
        <div class="map-links">
          <?php if ($restaurant['map_latitude'] && $restaurant['map_longitude']): ?>
            <a href="https://www.google.com/maps?q=<?php echo htmlspecialchars($restaurant['map_latitude']); ?>,<?php echo htmlspecialchars($restaurant['map_longitude']); ?>" target="_blank">View larger map</a>
          <?php endif; ?>
        </div>
        
        <div class="map-footer">
          <span>Map data ©<?php echo date('Y'); ?></span>
        </div>
        
        <?php if ($restaurant['map_latitude'] && $restaurant['map_longitude']): ?>
          <div style="margin-top: 30px;">
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo htmlspecialchars($restaurant['map_latitude']); ?>,<?php echo htmlspecialchars($restaurant['map_longitude']); ?>" target="_blank" class="btn btn-primary">Get Directions</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Brand Footer -->
<section class="brand-footer">
  <div class="container">
    <div class="brand-grid">
      <div class="brand-left">
        <div class="logo"><?php echo htmlspecialchars($restaurant['name']); ?></div>
        <h2>Welcome to enjoy happiness</h2>
        <?php if ($restaurant['footer_content']): ?>
          <div class="visit-text"><?php echo nl2br(htmlspecialchars($restaurant['footer_content'])); ?></div>
        <?php else: ?>
          <p class="visit-text">At <?php echo htmlspecialchars($restaurant['name']); ?>, our story began with a simple love for great service.</p>
          <p class="visit-text">Our mission is to bring fun and relaxation to the regular way of doing things.</p>
          <p class="visit-text">Join us and taste the difference passion and quality make.</p>
        <?php endif; ?>
        
        <div class="social-icons">
          <?php if (!empty($restaurant['instagram_url'])): ?>
            <a href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank" rel="noopener" title="Instagram">
              <svg class="social-icon-svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($restaurant['facebook_url'])): ?>
            <a href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank" rel="noopener" title="Facebook">
              <svg class="social-icon-svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($restaurant['twitter_url'])): ?>
            <a href="<?php echo htmlspecialchars($restaurant['twitter_url']); ?>" target="_blank" rel="noopener" title="Twitter">
              <svg class="social-icon-svg" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
            </a>
          <?php endif; ?>
          <?php if (!empty($restaurant['whatsapp_link'])): ?>
            <a href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" rel="noopener" title="WhatsApp">
              <svg class="social-icon-svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
          <?php endif; ?>
        </div>
        
        <button class="btn btn-outline" onclick="scrollToFirstMenu()">Explore menu</button>
      </div>
      
      <div class="brand-right">
        <div class="logo"><?php echo htmlspecialchars($restaurant['name']); ?></div>
        <h3>Visit Us</h3>
        
        <?php if ($restaurant['opening_hours']): ?>
          <div class="hours">
            <p><strong>Opening hours:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($restaurant['opening_hours'])); ?></p>
          </div>
        <?php endif; ?>
        
        <?php if ($restaurant['phone']): ?>
          <p><strong>Phone:</strong></p>
          <p><?php echo htmlspecialchars($restaurant['phone']); ?></p>
        <?php endif; ?>
        
        <?php if ($restaurant['address']): ?>
          <div class="address">
            <p><?php echo htmlspecialchars($restaurant['address']); ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Back to top button -->
<a id="scrollToTop" href="#" class="scroll-to-top" aria-label="Scroll to top" style="position:fixed;bottom:24px;right:24px;z-index:30;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:50%;background:#111;color:#fff;opacity:0;visibility:hidden;transform:translateY(10px);transition:opacity 0.3s,visibility 0.3s,transform 0.3s;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
</a>
<script>
(function(){var btn=document.getElementById('scrollToTop');if(btn){window.addEventListener('scroll',function(){var st=window.pageYOffset||document.documentElement.scrollTop;var dh=document.documentElement.scrollHeight-window.innerHeight;if(dh>0&&st>=dh*0.3){btn.style.opacity='1';btn.style.visibility='visible';btn.style.transform='translateY(0)';}else{btn.style.opacity='0';btn.style.visibility='hidden';btn.style.transform='translateY(10px)';}});btn.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>

<!-- Main Footer -->
<footer class="main-footer">
  <div class="container">
    <div class="footer-content">
      <div>© <?php echo date('Y'); ?> — <?php echo htmlspecialchars($restaurant['name']); ?></div>
      <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Cookies</a>
        <a href="#">Terms & Conditions</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>
