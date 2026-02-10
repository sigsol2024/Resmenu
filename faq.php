<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - SigSol Resmenu</title>
    <meta name="description" content="Frequently asked questions about SigSol Resmenu platform">
    
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
        ['label' => 'FAQ']
    ];
    include __DIR__ . '/includes/header.php'; 
    include __DIR__ . '/includes/breadcrumb.php'; 
    ?>
    
    <section class="section">
        <div class="section-title">
            <h2>Frequently Asked Questions</h2>
            <p class="section-description">
                Find answers to common questions about SigSol Resmenu
            </p>
        </div>
        
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">What is SigSol Resmenu?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        SigSol Resmenu is a professional digital menu platform that helps restaurants create beautiful, 
                        customizable online menus. It's designed to be easy to use, mobile-responsive, and perfect for 
                        showcasing your restaurant's offerings to customers.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Do I need technical skills to use SigSol Resmenu?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        No technical skills required! Our platform is designed to be intuitive and user-friendly. 
                        You can create and update your menu in minutes using our simple interface. If you need help, 
                        our support team is always available to assist you.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Can I customize my menu design?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Yes! You can fully customize your menu's appearance including colors, fonts, images, and layout. 
                        Choose from our professional templates or create your own unique design that matches your restaurant's brand.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Is my menu mobile-friendly?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Absolutely! All menus created with SigSol Resmenu are fully responsive and look perfect on desktop, 
                        tablet, and mobile devices. Your customers can browse your menu from any device.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">How much does it cost?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        We offer flexible pricing plans starting from ₦9,999/month. Choose between Starter, Professional, 
                        or Enterprise plans based on your needs. Annual plans save you 20% compared to monthly billing.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Can I update my menu anytime?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Yes! You can update your menu anytime, anywhere. Changes are reflected immediately on your live menu. 
                        Add new items, update prices, change descriptions, or modify categories with just a few clicks.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">How many menu items can I add?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        All our plans include unlimited menu items. Add as many categories and items as you need without 
                        worrying about limits.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Do you offer customer support?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Yes! We offer email support for all plans, and priority support for Professional and Enterprise plans. 
                        Our support team is here to help you succeed with your digital menu.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Can I use my own domain?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Yes! Enterprise plan customers can use their own custom domain. Contact our sales team for more 
                        information about custom domain setup.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">Is there a free trial?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Yes! You can start with a free trial to explore all features. No credit card required. 
                        Sign up today and see how easy it is to create your digital menu.
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question">How do I get started?</div>
                <div class="faq-answer">
                    <div class="faq-answer-content">
                        Getting started is easy! Simply click "Get Started" to create your account. You'll be guided 
                        through the setup process step by step. You can have your menu live in minutes.
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        // FAQ Accordion
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(function(item) {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', function() {
                    const isActive = item.classList.contains('active');
                    
                    // Close all items
                    faqItems.forEach(function(otherItem) {
                        otherItem.classList.remove('active');
                    });
                    
                    // Open clicked item if it wasn't active
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>

