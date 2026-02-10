<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - SigSol Resmenu</title>
    <meta name="description" content="Get in touch with SigSol Resmenu support team">
    
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
        ['label' => 'Contact']
    ];
    include __DIR__ . '/includes/header.php'; 
    include __DIR__ . '/includes/breadcrumb.php'; 
    ?>
    
    <section class="section">
        <div class="section-title">
            <h2>Get In Touch</h2>
            <p class="section-description">
                Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
            </p>
        </div>
        
        <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-top: 40px;">
            <!-- Contact Form -->
            <div>
                <h3 style="margin-bottom: 24px;">Send us a Message</h3>
                <form id="contactForm" class="contact-form">
                    <div class="form-group">
                        <label class="form-label" for="name">Your Name</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="message">Message</label>
                        <textarea id="message" name="message" class="form-textarea" rows="6" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    
                    <div id="formMessage" style="margin-top: 20px; padding: 12px; border-radius: 8px; display: none;"></div>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div>
                <h3 style="margin-bottom: 24px;">Contact Information</h3>
                
                <div style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 12px; color: #667eea;">📧 Email</h4>
                    <p style="color: #6b7280;">support@sigsolresmenu.com</p>
                    <p style="color: #6b7280;">info@sigsolresmenu.com</p>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 12px; color: #667eea;">📞 Phone</h4>
                    <p style="color: #6b7280;">+234 (0) 123 456 7890</p>
                    <p style="color: #6b7280; font-size: 14px;">Monday - Friday, 9 AM - 6 PM</p>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 12px; color: #667eea;">📍 Address</h4>
                    <p style="color: #6b7280;">
                        123 Business Street<br>
                        Lagos, Nigeria<br>
                        100001
                    </p>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 12px; color: #667eea;">🌐 Social Media</h4>
                    <div class="social-links" style="margin-top: 12px;">
                        <a href="#" target="_blank" rel="noopener">Facebook</a>
                        <a href="#" target="_blank" rel="noopener">Twitter</a>
                        <a href="#" target="_blank" rel="noopener">Instagram</a>
                        <a href="#" target="_blank" rel="noopener">LinkedIn</a>
                    </div>
                </div>
                
                <!-- Map Placeholder -->
                <div style="margin-top: 40px; height: 300px; background: #f0f0f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                    <div style="text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 12px;">📍</div>
                        <p>Map Placeholder</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        // Contact Form Handler
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            const messageDiv = document.getElementById('formMessage');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get form data
                    const formData = new FormData(form);
                    const data = {
                        name: formData.get('name'),
                        email: formData.get('email'),
                        subject: formData.get('subject'),
                        message: formData.get('message')
                    };
                    
                    // Show success message (in real implementation, this would send to server)
                    messageDiv.style.display = 'block';
                    messageDiv.style.background = '#d4edda';
                    messageDiv.style.color = '#155724';
                    messageDiv.style.border = '1px solid #c3e6cb';
                    messageDiv.textContent = 'Thank you for your message! We\'ll get back to you soon.';
                    
                    // Reset form
                    form.reset();
                    
                    // Hide message after 5 seconds
                    setTimeout(function() {
                        messageDiv.style.display = 'none';
                    }, 5000);
                });
            }
        });
    </script>
    
    <style>
        @media (max-width: 768px) {
            .section > div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</body>
</html>

