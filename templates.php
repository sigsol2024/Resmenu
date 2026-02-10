<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Showcase - SigSol Resmenu</title>
    <meta name="description" content="Browse our collection of beautiful restaurant menu templates">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Poppins:wght@500;700;900&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/marketing.css">
</head>
<body>
    <?php 
    $breadcrumbs = [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Templates']
    ];
    include __DIR__ . '/includes/header.php'; 
    include __DIR__ . '/includes/breadcrumb.php'; 
    ?>
    
    <section class="section">
        <div class="section-title">
            <h2>Choose Your Perfect Template</h2>
            <p class="section-description">
                Browse our collection of professionally designed templates. Each template is fully customizable 
                and optimized for all devices.
            </p>
        </div>
        
        <div class="template-grid">
            <!-- Template 1: Modern Classic -->
            <?php
            // Get first active restaurant for demo (or use a specific demo restaurant)
            require_once __DIR__ . '/includes/functions.php';
            $demoRestaurants = getAllActiveRestaurants();
            $demoSlug = !empty($demoRestaurants) ? $demoRestaurants[0]['slug'] : 'demo-restaurant';
            ?>
            <div class="template-card">
                <iframe 
                    src="/restaurant/<?php echo htmlspecialchars($demoSlug); ?>?template=1" 
                    class="template-preview"
                    title="Modern Classic Template Preview"
                    loading="lazy">
                </iframe>
                <div class="template-info">
                    <h3 class="template-name">Modern Classic</h3>
                    <p class="template-description">
                        Clean, modern design with rounded corners and elegant typography. Perfect for restaurants 
                        looking for a sophisticated yet approachable aesthetic.
                    </p>
                    <a href="/restaurant/<?php echo htmlspecialchars($demoSlug); ?>?template=1" target="_blank" class="btn btn-primary" style="width: 100%; margin-top: 16px;">
                        View Live Demo
                    </a>
                </div>
            </div>
            
            <!-- Template 2: Coming Soon Placeholder -->
            <div class="template-card">
                <div class="template-preview" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; flex-direction: column;">
                    <div style="font-size: 64px; margin-bottom: 16px;">🎨</div>
                    <h3 style="color: white; margin-bottom: 8px;">Template 2</h3>
                    <p style="color: rgba(255,255,255,0.9);">Coming Soon</p>
                </div>
                <div class="template-info">
                    <h3 class="template-name">Template 2</h3>
                    <p class="template-description">
                        A new template design is coming soon. Check back for updates!
                    </p>
                    <button class="btn btn-secondary" style="width: 100%; margin-top: 16px;" disabled>
                        Coming Soon
                    </button>
                </div>
            </div>
            
            <!-- Template 3: Coming Soon Placeholder -->
            <div class="template-card">
                <div class="template-preview" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; flex-direction: column;">
                    <div style="font-size: 64px; margin-bottom: 16px;">🍽️</div>
                    <h3 style="color: white; margin-bottom: 8px;">Template 3</h3>
                    <p style="color: rgba(255,255,255,0.9);">Coming Soon</p>
                </div>
                <div class="template-info">
                    <h3 class="template-name">Template 3</h3>
                    <p class="template-description">
                        A new template design is coming soon. Check back for updates!
                    </p>
                    <button class="btn btn-secondary" style="width: 100%; margin-top: 16px;" disabled>
                        Coming Soon
                    </button>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 60px; padding: 40px; background: var(--background-light); border-radius: 16px;">
            <h3 style="margin-bottom: 16px;">Need a Custom Template?</h3>
            <p style="color: #6b7280; margin-bottom: 24px;">
                We can create a custom template design specifically for your restaurant brand.
            </p>
            <a href="/contact.php" class="btn btn-primary">Contact Us</a>
        </div>
    </section>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <style>
        .template-preview {
            border: none;
            border-radius: 12px 12px 0 0;
            background: #f9fafb;
        }
        
        /* Ensure iframe doesn't break layout */
        iframe.template-preview {
            pointer-events: none; /* Prevent interaction in preview */
        }
        
        .template-card:hover iframe.template-preview {
            pointer-events: auto; /* Allow interaction on hover */
        }
    </style>
</body>
</html>

