<?php
/**
 * Email Templates for Subscription System
 * 
 * Templates for trial ending, payment reminders, and notifications
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/subscription.php';

/**
 * Get email header HTML
 */
function getEmailHeader($title = 'Restaurant Menu Platform') {
    return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f4f4f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .card { background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 32px; }
        .content h2 { color: #1f2937; font-size: 20px; margin: 0 0 16px; }
        .content p { color: #4b5563; font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .highlight { background: #f3f4f6; border-radius: 8px; padding: 16px; margin: 20px 0; }
        .highlight-value { font-size: 28px; font-weight: 700; color: #1f2937; }
        .highlight-label { font-size: 13px; color: #6b7280; text-transform: uppercase; }
        .btn { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 15px; margin: 16px 0; }
        .btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .btn-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .footer { padding: 24px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: center; }
        .footer p { color: #9ca3af; font-size: 13px; margin: 0; }
        .footer a { color: #6366f1; text-decoration: none; }
        .details-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .details-table td { padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .details-table td:first-child { color: #6b7280; font-size: 14px; }
        .details-table td:last-child { color: #1f2937; font-weight: 500; text-align: right; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
<div class="card">';
}

/**
 * Get email footer HTML
 */
function getEmailFooter() {
    $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://yoursite.com';
    return '<div class="footer">
    <p>© ' . date('Y') . ' Restaurant Menu Platform. All rights reserved.</p>
    <p><a href="' . $baseUrl . '">Visit our website</a></p>
</div>
</div>
</div>
</body>
</html>';
}

/**
 * Trial Ending Soon Email
 */
function getTrialEndingEmail($restaurant, $subscription, $daysRemaining) {
    $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://yoursite.com';
    
    $html = getEmailHeader('Trial Ending Soon');
    $html .= '<div class="header">
        <h1>Your Free Trial is Ending Soon</h1>
    </div>
    <div class="content">
        <h2>Hi ' . htmlspecialchars($restaurant['name']) . ',</h2>
        <p>Your free trial of Restaurant Menu Platform is ending soon. Don\'t lose access to your digital menu!</p>
        
        <div class="highlight" style="text-align: center;">
            <div class="highlight-value">' . $daysRemaining . ' Days</div>
            <div class="highlight-label">Remaining in your trial</div>
        </div>
        
        <p>To continue using all features including:</p>
        <ul style="color: #4b5563; line-height: 2;">
            <li>Digital menu management</li>
            <li>QR code generation</li>
            <li>Menu customization</li>
            <li>Analytics and insights</li>
        </ul>
        
        <p style="text-align: center;">
            <a href="' . $baseUrl . '/manager/billing.php" class="btn btn-warning">Subscribe Now</a>
        </p>
        
        <p>If you have any questions, feel free to contact our support team.</p>
    </div>';
    $html .= getEmailFooter();
    
    return $html;
}

/**
 * Trial Expired Email
 */
function getTrialExpiredEmail($restaurant) {
    $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://yoursite.com';
    
    $html = getEmailHeader('Trial Expired');
    $html .= '<div class="header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
        <h1>Your Free Trial Has Ended</h1>
    </div>
    <div class="content">
        <h2>Hi ' . htmlspecialchars($restaurant['name']) . ',</h2>
        <p>Your free trial has expired. Your menu is no longer accessible to customers until you subscribe.</p>
        
        <p>Subscribe now to restore access and continue:</p>
        <ul style="color: #4b5563; line-height: 2;">
            <li>Managing your digital menu</li>
            <li>Generating QR codes</li>
            <li>Tracking customer scans</li>
            <li>Customizing your menu design</li>
        </ul>
        
        <p style="text-align: center;">
            <a href="' . $baseUrl . '/manager/billing.php" class="btn btn-danger">Subscribe Now</a>
        </p>
    </div>';
    $html .= getEmailFooter();
    
    return $html;
}

/**
 * Payment Reminder Email
 */
function getPaymentReminderEmail($restaurant, $subscription, $daysUntilExpiry) {
    $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://yoursite.com';
    $plan = getSubscriptionPlan($subscription['plan_id']);
    $amount = $subscription['billing_cycle'] === 'annual' ? $plan['annual_price'] : $plan['monthly_price'];
    
    $html = getEmailHeader('Payment Reminder');
    $html .= '<div class="header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <h1>Subscription Renewal Reminder</h1>
    </div>
    <div class="content">
        <h2>Hi ' . htmlspecialchars($restaurant['name']) . ',</h2>
        <p>Your subscription will expire in ' . $daysUntilExpiry . ' day' . ($daysUntilExpiry > 1 ? 's' : '') . '. Renew now to avoid any service interruption.</p>
        
        <table class="details-table">
            <tr>
                <td>Plan</td>
                <td>' . htmlspecialchars($plan['name']) . '</td>
            </tr>
            <tr>
                <td>Billing Cycle</td>
                <td>' . ucfirst($subscription['billing_cycle']) . '</td>
            </tr>
            <tr>
                <td>Renewal Amount</td>
                <td>' . formatSubscriptionPrice($amount) . '</td>
            </tr>
            <tr>
                <td>Expiry Date</td>
                <td>' . date('F j, Y', strtotime($subscription['current_period_end'])) . '</td>
            </tr>
        </table>
        
        <p style="text-align: center;">
            <a href="' . $baseUrl . '/manager/billing.php" class="btn btn-warning">Renew Subscription</a>
        </p>
    </div>';
    $html .= getEmailFooter();
    
    return $html;
}

/**
 * Payment Success Email
 */
function getPaymentSuccessEmail($restaurant, $payment, $subscription) {
    $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://yoursite.com';
    $plan = getSubscriptionPlan($subscription['plan_id']);
    
    $html = getEmailHeader('Payment Successful');
    $html .= '<div class="header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <h1>Payment Successful!</h1>
    </div>
    <div class="content">
        <h2>Thank you, ' . htmlspecialchars($restaurant['name']) . '!</h2>
        <p>Your payment has been processed successfully. Your subscription is now active.</p>
        
        <table class="details-table">
            <tr>
                <td>Amount Paid</td>
                <td>' . formatSubscriptionPrice($payment['amount'], $payment['currency']) . '</td>
            </tr>
            <tr>
                <td>Plan</td>
                <td>' . htmlspecialchars($plan['name']) . '</td>
            </tr>
            <tr>
                <td>Billing Cycle</td>
                <td>' . ucfirst($subscription['billing_cycle']) . '</td>
            </tr>
            <tr>
                <td>Transaction Reference</td>
                <td style="font-family: monospace;">' . htmlspecialchars($payment['transaction_reference']) . '</td>
            </tr>
            <tr>
                <td>Payment Date</td>
                <td>' . date('F j, Y g:i A', strtotime($payment['paid_at'] ?? $payment['created_at'])) . '</td>
            </tr>
            <tr>
                <td>Next Renewal</td>
                <td>' . date('F j, Y', strtotime($subscription['current_period_end'])) . '</td>
            </tr>
        </table>
        
        <p style="text-align: center;">
            <a href="' . $baseUrl . '/manager/billing.php" class="btn">View Billing Details</a>
        </p>
    </div>';
    $html .= getEmailFooter();
    
    return $html;
}

/**
 * Subscription Expired Email
 */
function getSubscriptionExpiredEmail($restaurant) {
    $baseUrl = defined('SITE_URL') ? SITE_URL : 'https://yoursite.com';
    
    $html = getEmailHeader('Subscription Expired');
    $html .= '<div class="header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
        <h1>Your Subscription Has Expired</h1>
    </div>
    <div class="content">
        <h2>Hi ' . htmlspecialchars($restaurant['name']) . ',</h2>
        <p>Your subscription has expired and your menu is no longer accessible to customers.</p>
        
        <p>To restore access to your digital menu, please renew your subscription immediately.</p>
        
        <p style="text-align: center;">
            <a href="' . $baseUrl . '/manager/billing.php" class="btn btn-danger">Renew Now</a>
        </p>
        
        <p>If you believe this is an error or need assistance, please contact our support team.</p>
    </div>';
    $html .= getEmailFooter();
    
    return $html;
}

/**
 * Admin Notification - New Payment (uses site template with site name/logo)
 */
function getAdminPaymentNotificationEmail($restaurant, $payment, $subscription) {
    $plan = getSubscriptionPlan($subscription['plan_id']);

    $body = '<h2 style="margin:0 0 16px;font-size:22px;font-weight:700;color:#111827;">Payment Notification</h2>
        <p>A new payment has been received from a restaurant.</p>
        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
            <tr><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;color:#6b7280;">Restaurant</td><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:500;">' . htmlspecialchars($restaurant['name']) . '</td></tr>
            <tr><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;color:#6b7280;">Amount</td><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:500;">' . formatSubscriptionPrice($payment['amount'], $payment['currency']) . '</td></tr>
            <tr><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;color:#6b7280;">Plan</td><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:500;">' . htmlspecialchars($plan['name']) . ' (' . ucfirst($subscription['billing_cycle']) . ')</td></tr>
            <tr><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;color:#6b7280;">Gateway</td><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:500;">' . ucfirst($payment['payment_gateway']) . '</td></tr>
            <tr><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;color:#6b7280;">Reference</td><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;text-align:right;font-family:monospace;font-size:13px;">' . htmlspecialchars($payment['transaction_reference']) . '</td></tr>
            <tr><td style="padding:12px 0;color:#6b7280;">Date</td><td style="padding:12px 0;text-align:right;font-weight:500;">' . date('F j, Y g:i A') . '</td></tr>
        </table>';

    return getSiteEmailTemplate('New Payment Received', $body);
}

/**
 * Send email using central mail service (PHPMailer SMTP or PHP mail() fallback)
 */
function sendSubscriptionEmail($to, $subject, $htmlBody) {
    require_once __DIR__ . '/mail.php';
    $siteSettings = getSiteSettings();
    $siteName = $siteSettings['site_name'] ?? 'Resmenu';
    return sendEmail($to, '', $subject, $htmlBody, [
        'from_name' => $siteName,
    ]);
}

// Note: wasEmailSent, recordEmailSent, and formatSubscriptionPrice are defined in includes/subscription.php
// which is required at the top of this file

