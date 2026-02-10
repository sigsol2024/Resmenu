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
        <?php if (!empty($restaurant['logo'])): ?>
          <img src="<?php echo $uploadBaseUrl . '/logos/' . htmlspecialchars($restaurant['logo']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>" style="max-height: 40px; width: auto;">
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
          <button class="btn btn-primary" onclick="scrollToFirstMenu()">Place an order</button>
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
          <?php if ($restaurant['whatsapp_link']): ?>
            <a href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" class="btn btn-primary">Make a Reservation</a>
          <?php else: ?>
            <button class="btn btn-primary">Make a Reservation</button>
          <?php endif; ?>
          <button class="btn btn-outline" onclick="scrollToFirstMenu()">Place an Order</button>
        </div>
      </div>
      
      <div class="hero-image-container">
        <?php if (!empty($restaurant['hero_image'])): ?>
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
          <?php if ($restaurant['whatsapp_link']): ?>
            <a href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" class="btn btn-primary overlay-btn">Book Now</a>
          <?php else: ?>
            <button class="btn btn-primary overlay-btn">Book Now</button>
          <?php endif; ?>
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
          <?php if ($restaurant['whatsapp_link']): ?>
            <a href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" class="btn btn-primary">Place an order</a>
          <?php else: ?>
            <button class="btn btn-primary">Place an order</button>
          <?php endif; ?>
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
          <?php if ($restaurant['instagram_url']): ?>
            <a href="<?php echo htmlspecialchars($restaurant['instagram_url']); ?>" target="_blank">📷</a>
          <?php endif; ?>
          <?php if ($restaurant['facebook_url']): ?>
            <a href="<?php echo htmlspecialchars($restaurant['facebook_url']); ?>" target="_blank">📘</a>
          <?php endif; ?>
          <?php if ($restaurant['twitter_url']): ?>
            <a href="<?php echo htmlspecialchars($restaurant['twitter_url']); ?>" target="_blank">🐦</a>
          <?php endif; ?>
        </div>
        
        <?php if ($restaurant['whatsapp_link']): ?>
          <a href="<?php echo htmlspecialchars($restaurant['whatsapp_link']); ?>" target="_blank" class="btn btn-primary" style="margin-right: 12px;">Place order</a>
        <?php else: ?>
          <button class="btn btn-primary" style="margin-right: 12px;">Place order</button>
        <?php endif; ?>
        <button class="btn btn-outline">Explore menu</button>
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
