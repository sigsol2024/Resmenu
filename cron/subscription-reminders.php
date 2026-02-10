<?php
/**
 * Subscription Reminders Cron Job
 * 
 * Run this script daily via cron:
 * 0 9 * * * /usr/bin/php /path/to/cron/subscription-reminders.php
 * 
 * Or on Windows Task Scheduler:
 * php C:\path\to\cron\subscription-reminders.php
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/email-templates.php';

$pdo = getDBConnection();

if (!$pdo) {
    error_log('Subscription reminders: Failed to connect to database');
    exit(1);
}

echo "Starting subscription reminders cron job...\n";
$startTime = time();
$emailsSent = 0;
$errors = 0;

// Get admin email for notifications
$adminEmail = null;
try {
    $stmt = $pdo->query("SELECT email FROM admins LIMIT 1");
    $admin = $stmt->fetch();
    $adminEmail = $admin ? $admin['email'] : null;
} catch (PDOException $e) {
    error_log('Could not fetch admin email: ' . $e->getMessage());
}

/**
 * Process trial ending reminders
 * Send reminders at 3 days and 1 day before trial ends
 */
function processTrialReminders($pdo, &$emailsSent, &$errors) {
    echo "Processing trial reminders...\n";
    
    $reminderDays = [3, 1]; // Days before trial ends to send reminders
    
    foreach ($reminderDays as $days) {
        $targetDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $sql = "
            SELECT s.*, r.name as restaurant_name, r.email as restaurant_email, m.email as manager_email
            FROM subscriptions s
            JOIN restaurants r ON s.restaurant_id = r.id
            LEFT JOIN managers m ON m.restaurant_id = r.id
            WHERE s.status = 'trial'
            AND DATE(s.trial_ends_at) = ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$targetDate]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($subscriptions as $sub) {
            // Check if email already sent
            if (wasEmailSent($sub['id'], 'trial_ending', $days)) {
                continue;
            }
            
            $email = $sub['manager_email'] ?: $sub['restaurant_email'];
            if (!$email) continue;
            
            $restaurant = ['name' => $sub['restaurant_name'], 'email' => $email];
            $subject = "Your trial ends in {$days} day" . ($days > 1 ? 's' : '') . " - Restaurant Menu Platform";
            $body = getTrialEndingEmail($restaurant, $sub, $days);
            
            if (sendSubscriptionEmail($email, $subject, $body)) {
                recordEmailSent($sub['id'], 'trial_ending', $days);
                $emailsSent++;
                echo "  Sent trial reminder to {$email} ({$days} days)\n";
            } else {
                $errors++;
                echo "  Failed to send trial reminder to {$email}\n";
            }
        }
    }
}

/**
 * Process trial expired notifications
 */
function processTrialExpired($pdo, &$emailsSent, &$errors) {
    echo "Processing expired trials...\n";
    
    $sql = "
        SELECT s.*, r.name as restaurant_name, r.email as restaurant_email, m.email as manager_email
        FROM subscriptions s
        JOIN restaurants r ON s.restaurant_id = r.id
        LEFT JOIN managers m ON m.restaurant_id = r.id
        WHERE s.status = 'trial'
        AND s.trial_ends_at < NOW()
    ";
    
    $stmt = $pdo->query($sql);
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subscriptions as $sub) {
        // Update status to expired
        $pdo->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = ?")->execute([$sub['id']]);
        
        // Check if email already sent
        if (wasEmailSent($sub['id'], 'trial_expired', null)) {
            continue;
        }
        
        $email = $sub['manager_email'] ?: $sub['restaurant_email'];
        if (!$email) continue;
        
        $restaurant = ['name' => $sub['restaurant_name'], 'email' => $email];
        $subject = "Your trial has ended - Restaurant Menu Platform";
        $body = getTrialExpiredEmail($restaurant);
        
        if (sendSubscriptionEmail($email, $subject, $body)) {
            recordEmailSent($sub['id'], 'trial_expired', null);
            $emailsSent++;
            echo "  Sent trial expired notification to {$email}\n";
        } else {
            $errors++;
            echo "  Failed to send trial expired notification to {$email}\n";
        }
    }
}

/**
 * Process payment reminders for active subscriptions
 * Monthly: 15, 7, 3, 1 days before
 * Annual: 30, 15, 7, 1 days before
 */
function processPaymentReminders($pdo, &$emailsSent, &$errors) {
    echo "Processing payment reminders...\n";
    
    $monthlyDays = [15, 7, 3, 1];
    $annualDays = [30, 15, 7, 1];
    
    // Get active subscriptions
    $sql = "
        SELECT s.*, r.name as restaurant_name, r.email as restaurant_email, m.email as manager_email
        FROM subscriptions s
        JOIN restaurants r ON s.restaurant_id = r.id
        LEFT JOIN managers m ON m.restaurant_id = r.id
        WHERE s.status = 'active'
        AND s.current_period_end IS NOT NULL
    ";
    
    $stmt = $pdo->query($sql);
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subscriptions as $sub) {
        $reminderDays = $sub['billing_cycle'] === 'annual' ? $annualDays : $monthlyDays;
        $expiryDate = strtotime($sub['current_period_end']);
        $daysUntilExpiry = ceil(($expiryDate - time()) / 86400);
        
        // Check if we need to send a reminder
        if (!in_array($daysUntilExpiry, $reminderDays)) {
            continue;
        }
        
        // Check if email already sent
        if (wasEmailSent($sub['id'], 'payment_reminder', $daysUntilExpiry)) {
            continue;
        }
        
        $email = $sub['manager_email'] ?: $sub['restaurant_email'];
        if (!$email) continue;
        
        $restaurant = ['name' => $sub['restaurant_name'], 'email' => $email];
        $subject = "Subscription renewal in {$daysUntilExpiry} day" . ($daysUntilExpiry > 1 ? 's' : '') . " - Restaurant Menu Platform";
        $body = getPaymentReminderEmail($restaurant, $sub, $daysUntilExpiry);
        
        if (sendSubscriptionEmail($email, $subject, $body)) {
            recordEmailSent($sub['id'], 'payment_reminder', $daysUntilExpiry);
            $emailsSent++;
            echo "  Sent payment reminder to {$email} ({$daysUntilExpiry} days)\n";
        } else {
            $errors++;
            echo "  Failed to send payment reminder to {$email}\n";
        }
    }
}

/**
 * Process subscription expiration
 */
function processSubscriptionExpired($pdo, &$emailsSent, &$errors) {
    echo "Processing expired subscriptions...\n";
    
    $sql = "
        SELECT s.*, r.name as restaurant_name, r.email as restaurant_email, m.email as manager_email
        FROM subscriptions s
        JOIN restaurants r ON s.restaurant_id = r.id
        LEFT JOIN managers m ON m.restaurant_id = r.id
        WHERE s.status = 'active'
        AND s.current_period_end < NOW()
    ";
    
    $stmt = $pdo->query($sql);
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($subscriptions as $sub) {
        // Update status to expired
        $pdo->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = ?")->execute([$sub['id']]);
        
        // Check if email already sent
        if (wasEmailSent($sub['id'], 'subscription_expired', null)) {
            continue;
        }
        
        $email = $sub['manager_email'] ?: $sub['restaurant_email'];
        if (!$email) continue;
        
        $restaurant = ['name' => $sub['restaurant_name'], 'email' => $email];
        $subject = "Your subscription has expired - Restaurant Menu Platform";
        $body = getSubscriptionExpiredEmail($restaurant);
        
        if (sendSubscriptionEmail($email, $subject, $body)) {
            recordEmailSent($sub['id'], 'subscription_expired', null);
            $emailsSent++;
            echo "  Sent subscription expired notification to {$email}\n";
        } else {
            $errors++;
            echo "  Failed to send subscription expired notification to {$email}\n";
        }
    }
}

// Run all processors
try {
    processTrialReminders($pdo, $emailsSent, $errors);
    processTrialExpired($pdo, $emailsSent, $errors);
    processPaymentReminders($pdo, $emailsSent, $errors);
    processSubscriptionExpired($pdo, $emailsSent, $errors);
} catch (Exception $e) {
    error_log('Subscription reminders error: ' . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
    $errors++;
}

// Summary
$duration = time() - $startTime;
echo "\n";
echo "=================================\n";
echo "Subscription Reminders Summary\n";
echo "=================================\n";
echo "Emails Sent: {$emailsSent}\n";
echo "Errors: {$errors}\n";
echo "Duration: {$duration} seconds\n";
echo "Completed at: " . date('Y-m-d H:i:s') . "\n";

// Log summary
error_log("Subscription reminders: Sent {$emailsSent} emails, {$errors} errors, took {$duration}s");

exit($errors > 0 ? 1 : 0);

