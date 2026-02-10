<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - SigSol Resmenu</title>
    <meta name="description" content="Terms and conditions for using SigSol Resmenu platform">
    
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
        ['label' => 'Terms and Conditions']
    ];
    include __DIR__ . '/includes/header.php'; 
    include __DIR__ . '/includes/breadcrumb.php'; 
    ?>
    
    <section class="section">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 24px;">Terms and Conditions</h1>
            <p style="color: #6b7280; margin-bottom: 40px;">Last updated: <?php echo date('F j, Y'); ?></p>
            
            <div style="line-height: 1.8; color: #374151;">
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">1. Acceptance of Terms</h2>
                    <p>
                        By accessing and using SigSol Resmenu ("the Platform"), you accept and agree to be bound by the terms 
                        and provision of this agreement. If you do not agree to abide by the above, please do not use this service.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">2. Use License</h2>
                    <p>
                        Permission is granted to temporarily use SigSol Resmenu for personal and commercial purposes. This is the 
                        grant of a license, not a transfer of title, and under this license you may not:
                    </p>
                    <ul style="margin-left: 24px; margin-top: 12px; color: #6b7280;">
                        <li>Modify or copy the materials</li>
                        <li>Use the materials for any commercial purpose without written consent</li>
                        <li>Attempt to decompile or reverse engineer any software</li>
                        <li>Remove any copyright or other proprietary notations</li>
                    </ul>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">3. Account Registration</h2>
                    <p>
                        To access certain features of the Platform, you must register for an account. You agree to provide 
                        accurate, current, and complete information during registration and to update such information to keep 
                        it accurate, current, and complete.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">4. User Content</h2>
                    <p>
                        You retain ownership of any content you submit to the Platform. By submitting content, you grant us a 
                        worldwide, non-exclusive, royalty-free license to use, reproduce, and distribute your content solely for 
                        the purpose of providing and improving the Platform.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">5. Payment Terms</h2>
                    <p>
                        Subscription fees are billed in advance on a monthly or annual basis. All fees are non-refundable except 
                        as required by law. You are responsible for any applicable taxes. We reserve the right to change our pricing 
                        with 30 days' notice.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">6. Cancellation and Refunds</h2>
                    <p>
                        You may cancel your subscription at any time. Cancellation takes effect at the end of your current billing 
                        period. Refunds are provided only as required by law or at our sole discretion.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">7. Prohibited Uses</h2>
                    <p>You agree not to use the Platform:</p>
                    <ul style="margin-left: 24px; margin-top: 12px; color: #6b7280;">
                        <li>For any unlawful purpose</li>
                        <li>To transmit any viruses or malicious code</li>
                        <li>To interfere with or disrupt the Platform</li>
                        <li>To impersonate any person or entity</li>
                        <li>To collect or store personal data about other users</li>
                    </ul>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">8. Intellectual Property</h2>
                    <p>
                        The Platform and its original content, features, and functionality are owned by SigSol Resmenu and are 
                        protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">9. Limitation of Liability</h2>
                    <p>
                        In no event shall SigSol Resmenu, its directors, employees, or agents be liable for any indirect, 
                        incidental, special, consequential, or punitive damages resulting from your use or inability to use the Platform.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">10. Changes to Terms</h2>
                    <p>
                        We reserve the right to modify these terms at any time. We will notify users of any material changes by 
                        posting the new terms on this page and updating the "Last updated" date.
                    </p>
                </div>
                
                <div style="margin-bottom: 40px;">
                    <h2 style="margin-bottom: 16px; color: #111827;">11. Contact Information</h2>
                    <p>
                        If you have any questions about these Terms and Conditions, please contact us at 
                        <a href="mailto:legal@sigsolresmenu.com" style="color: #667eea;">legal@sigsolresmenu.com</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

