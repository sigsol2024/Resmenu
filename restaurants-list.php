<?php
/**
 * Restaurants Listing Page
 * Dynamic page that fetches restaurants from API
 */
$breadcrumbs = [
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Restaurants']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants - SigSol Resmenu</title>
    <meta name="description" content="Browse all restaurants using SigSol Resmenu platform">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=Poppins:wght@500;700;900&display=swap" rel="stylesheet">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/marketing.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <?php include __DIR__ . '/includes/breadcrumb.php'; ?>
    
    <section class="section">
        <div class="section-title">
            <h2>Our Partner Restaurants</h2>
            <p class="section-description">
                Discover amazing restaurants and browse their digital menus
            </p>
        </div>
        
        <div id="restaurantsContainer">
            <div class="loading">
                <div class="spinner"></div>
                <p>Loading restaurants...</p>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        // Fetch restaurants from API
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('restaurantsContainer');
            
            fetch('/api/restaurants.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    container.innerHTML = '';
                    
                    if (data.success && data.data && data.data.length > 0) {
                        const grid = document.createElement('div');
                        grid.className = 'cards-grid';
                        
                        data.data.forEach(function(restaurant) {
                            const card = document.createElement('div');
                            card.className = 'card';
                            
                            // Escape HTML to prevent XSS
                            const escapeHtml = (text) => {
                                const div = document.createElement('div');
                                div.textContent = text;
                                return div.innerHTML;
                            };
                            
                            const name = escapeHtml(restaurant.name);
                            const description = restaurant.description ? escapeHtml(restaurant.description) : '';
                            const phone = restaurant.phone ? escapeHtml(restaurant.phone) : '';
                            const address = restaurant.address ? escapeHtml(restaurant.address) : '';
                            const slug = escapeHtml(restaurant.slug);
                            const logo = restaurant.logo ? escapeHtml(restaurant.logo) : '';
                            
                            card.innerHTML = `
                                ${logo ? `
                                    <img src="${logo}" alt="${name}" style="width: 100%; max-width: 200px; height: 150px; object-fit: contain; margin: 0 auto 20px; display: block; border-radius: 12px;">
                                ` : `
                                    <div style="width: 100%; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 12px; margin-bottom: 20px;">
                                        <span style="font-size: 48px;">🍽️</span>
                                    </div>
                                `}
                                <h3 style="text-align: center; margin-bottom: 10px;">${name}</h3>
                                ${description ? `
                                    <p style="text-align: center; color: #6b7280; margin-bottom: 15px; line-height: 1.6;">
                                        ${description.length > 120 ? description.substring(0, 120) + '...' : description}
                                    </p>
                                ` : ''}
                                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                                    ${phone ? `
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; color: #6b7280; font-size: 14px;">
                                            <span>📞</span>
                                            <span>${phone}</span>
                                        </div>
                                    ` : ''}
                                    ${address ? `
                                        <div style="display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 14px;">
                                            <span>📍</span>
                                            <span>${address.length > 50 ? address.substring(0, 50) + '...' : address}</span>
                                        </div>
                                    ` : ''}
                                </div>
                                <a href="/restaurant/${slug}" class="btn btn-primary" style="width: 100%; margin-top: 20px; text-align: center;">
                                    View Menu →
                                </a>
                            `;
                            
                            grid.appendChild(card);
                        });
                        
                        container.appendChild(grid);
                    } else {
                        container.innerHTML = `
                            <div class="empty-state">
                                <div class="empty-state-icon">🍽️</div>
                                <h3>No Restaurants Found</h3>
                                <p>Check back soon for new restaurants!</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching restaurants:', error);
                    container.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">⚠️</div>
                            <h3>Error Loading Restaurants</h3>
                            <p>Please try again later.</p>
                            <button onclick="location.reload()" class="btn btn-primary" style="margin-top: 20px;">Retry</button>
                        </div>
                    `;
                });
        });
    </script>
</body>
</html>

