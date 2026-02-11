<?php
/**
 * Admin Payment Gateway Settings
 * Configure Paystack and Flutterwave API keys
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/subscription.php';

// Ensure SITE_URL is defined
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $scriptDir = dirname($scriptPath);
    if ($scriptDir === '/admin' || $scriptDir === '/manager' || strpos($scriptDir, '/admin/') === 0 || strpos($scriptDir, '/manager/') === 0) {
        $basePath = dirname($scriptDir);
    } else {
        $basePath = $scriptDir;
    }
    $basePath = ($basePath === '/' || $basePath === '\\' || $basePath === '.') ? '' : $basePath;
    define('SITE_URL', $protocol . $host . $basePath);
}

$pdo = getDBConnection();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gateway = $_POST['gateway'] ?? '';
    
    if (in_array($gateway, ['paystack', 'flutterwave'])) {
        $settings = [
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'test_mode' => isset($_POST['test_mode']) ? 1 : 0,
            'public_key_test' => trim($_POST['public_key_test'] ?? ''),
            'webhook_secret_test' => trim($_POST['webhook_secret_test'] ?? ''),
            'public_key_live' => trim($_POST['public_key_live'] ?? ''),
            'webhook_secret_live' => trim($_POST['webhook_secret_live'] ?? '')
        ];
        
        // Only update secret keys if provided (not empty)
        if (!empty($_POST['secret_key_test'])) {
            $settings['secret_key_test'] = trim($_POST['secret_key_test']);
        }
        if (!empty($_POST['secret_key_live'])) {
            $settings['secret_key_live'] = trim($_POST['secret_key_live']);
        }
        
        if (updatePaymentSettings($gateway, $settings)) {
            $message = ucfirst($gateway) . ' settings updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update settings.';
            $messageType = 'error';
        }
    }
}

// Get current settings
$paystackSettings = getPaymentSettings('paystack');
$flutterwaveSettings = getPaymentSettings('flutterwave');

$pageTitle = 'Payment Gateway Settings';
include __DIR__ . '/../includes/admin-layout.php';
?>

<style>
/* Clean Payment Settings Styles */
.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text);
    margin: 0;
}

.page-subtitle {
    color: var(--muted);
    font-size: 0.875rem;
    margin-top: 4px;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 0.875rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Tabs */
.tabs-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
    overflow: hidden;
}

.tabs-nav {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.tab-button {
    flex: 1;
    padding: 14px 20px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tab-button:hover {
    background: #f3f4f6;
    color: #374151;
}

.tab-button.active {
    color: #111827;
    border-bottom-color: #111827;
    background: #fff;
}

.tab-button svg {
    width: 18px;
    height: 18px;
}

.tab-content {
    display: none;
    padding: 24px;
}

.tab-content.active {
    display: block;
}

/* Form Sections */
.settings-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 24px;
    margin-bottom: 24px;
}

.settings-card:last-child {
    margin-bottom: 0;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-title svg {
    width: 20px;
    height: 20px;
    color: #6b7280;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.active {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.inactive {
    background: #f3f4f6;
    color: #6b7280;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* Toggle Rows */
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: #f9fafb;
    border-radius: 6px;
    margin-bottom: 20px;
}

.toggle-info {
    flex: 1;
}

.toggle-label {
    font-weight: 500;
    color: #111827;
    font-size: 0.875rem;
    margin-bottom: 2px;
}

.toggle-description {
    font-size: 0.75rem;
    color: #6b7280;
}

.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #d1d5db;
    transition: .3s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

input:checked + .toggle-slider {
    background-color: #111827;
}

input:checked + .toggle-slider:before {
    transform: translateX(20px);
}

/* Form Groups */
.form-section {
    margin-bottom: 24px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-subtitle {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-subtitle .badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-test {
    background: #f3f4f6;
    color: #4b5563;
}

.badge-live {
    background: #d1fae5;
    color: #065f46;
}

.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
    font-size: 0.875rem;
}

.form-group input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    font-family: 'Consolas', 'Monaco', monospace;
    transition: border-color 0.2s;
    background: #fff;
}

.form-group input:focus {
    outline: none;
    border-color: #111827;
}

.form-group input[type="password"] {
    letter-spacing: 1px;
}

.form-hint {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 4px;
}

.webhook-url-wrap {
    display: flex;
    align-items: stretch;
    gap: 0;
    margin-top: 8px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
}
.webhook-url {
    flex: 1;
    background: #f9fafb;
    padding: 10px 12px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 0.8rem;
    word-break: break-all;
    color: #374151;
    border: none;
}
.webhook-copy-btn {
    background: #111827;
    color: #fff;
    border: none;
    padding: 10px 14px;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 500;
    white-space: nowrap;
    transition: background 0.2s;
}
.webhook-copy-btn:hover {
    background: #374151;
}
.webhook-copy-btn.copied {
    background: #065f46;
}

/* Save Button */
.save-section {
    padding: 20px 24px;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
}

.btn-save {
    background: #111827;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}

.btn-save:hover {
    background: #374151;
}

.btn-save svg {
    width: 16px;
    height: 16px;
}

/* Info Box */
.info-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 16px;
    margin-bottom: 24px;
}

.info-box-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-box-title svg {
    width: 18px;
    height: 18px;
    color: #6b7280;
}

.info-box-content {
    color: #6b7280;
    font-size: 0.813rem;
    line-height: 1.6;
}

.info-box-content ul {
    margin: 8px 0 0 20px;
    padding: 0;
}

.info-box-content li {
    margin-bottom: 4px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .tabs-nav {
        flex-direction: column;
    }
    
    .tab-button {
        border-bottom: 1px solid #e5e7eb;
        border-right: none;
    }
    
    .tab-button.active {
        border-bottom-color: #e5e7eb;
        border-left: 2px solid #111827;
    }
    
    .tab-content {
        padding: 20px 16px;
    }
    
    .settings-card {
        padding: 20px 16px;
    }
    
    .toggle-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .save-section {
        padding: 16px;
    }
    
    .btn-save {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Payment Gateway Settings</h1>
    <p class="page-subtitle">Configure your payment gateway API credentials</p>
</div>

<!-- Info Box -->
<div class="info-box">
    <div class="info-box-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Important Information
    </div>
    <div class="info-box-content">
        <ul>
            <li><strong>Test Mode:</strong> Use test keys during development. No real charges will be made.</li>
            <li><strong>Live Mode:</strong> Use live keys for production. Real payments will be processed.</li>
            <li><strong>Secret Keys:</strong> Never share your secret keys. They are encrypted before storage.</li>
            <li><strong>Webhook URLs:</strong> Copy each URL and add it to your Paystack/Flutterwave dashboard to receive subscription payment notifications.</li>
        </ul>
    </div>
</div>

<!-- Tabs Container -->
<div class="tabs-container">
    <div class="tabs-nav">
        <button class="tab-button active" onclick="switchTab('paystack')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Paystack
        </button>
        <button class="tab-button" onclick="switchTab('flutterwave')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Flutterwave
        </button>
    </div>
    
    <!-- Paystack Tab -->
    <div id="tab-paystack" class="tab-content active">
        <form method="POST" action="">
            <input type="hidden" name="gateway" value="paystack">
            
            <div class="settings-card">
                <div class="section-header">
                    <div class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Paystack Configuration
                    </div>
                    <span class="status-badge <?php echo !empty($paystackSettings['is_active']) ? 'active' : 'inactive'; ?>">
                        <span class="status-dot"></span>
                        <?php echo !empty($paystackSettings['is_active']) ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                
                <!-- Enable/Disable Toggle -->
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-label">Enable Paystack</div>
                        <div class="toggle-description">Allow customers to pay with Paystack</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_active" <?php echo !empty($paystackSettings['is_active']) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <!-- Test Mode Toggle -->
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-label">Test Mode</div>
                        <div class="toggle-description">Use test credentials (no real payments)</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="test_mode" id="paystack_test_mode" <?php echo !empty($paystackSettings['test_mode']) ? 'checked' : ''; ?> onchange="toggleTestCredentials('paystack', this.checked)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <!-- Test Credentials -->
            <div class="settings-card" id="paystack_test_credentials" style="display: <?php echo !empty($paystackSettings['test_mode']) ? 'block' : 'none'; ?>;">
                <div class="section-subtitle">
                    <span>Test Credentials</span>
                    <span class="badge badge-test">TEST</span>
                </div>
                <div class="form-group">
                    <label for="paystack_public_key_test">Public Key</label>
                    <input type="text" id="paystack_public_key_test" name="public_key_test" 
                           value="<?php echo htmlspecialchars($paystackSettings['public_key_test'] ?? ''); ?>"
                           placeholder="pk_test_xxxxxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="paystack_secret_key_test">Secret Key</label>
                    <input type="password" id="paystack_secret_key_test" name="secret_key_test" 
                           placeholder="<?php echo !empty($paystackSettings['secret_key_test']) ? '••••••••••••••••' : 'sk_test_xxxxxxxxxxxxx'; ?>">
                    <div class="form-hint">Leave empty to keep existing key</div>
                </div>
                <div class="form-group">
                    <label for="paystack_webhook_secret_test">Webhook Secret</label>
                    <input type="text" id="paystack_webhook_secret_test" name="webhook_secret_test" 
                           value="<?php echo htmlspecialchars($paystackSettings['webhook_secret_test'] ?? ''); ?>"
                           placeholder="Optional">
                </div>
            </div>
            
            <!-- Live Credentials -->
            <div class="settings-card">
                <div class="section-subtitle">
                    <span>Live Credentials</span>
                    <span class="badge badge-live">LIVE</span>
                </div>
                <div class="form-group">
                    <label for="paystack_public_key_live">Public Key</label>
                    <input type="text" id="paystack_public_key_live" name="public_key_live" 
                           value="<?php echo htmlspecialchars($paystackSettings['public_key_live'] ?? ''); ?>"
                           placeholder="pk_live_xxxxxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="paystack_secret_key_live">Secret Key</label>
                    <input type="password" id="paystack_secret_key_live" name="secret_key_live" 
                           placeholder="<?php echo !empty($paystackSettings['secret_key_live']) ? '••••••••••••••••' : 'sk_live_xxxxxxxxxxxxx'; ?>">
                    <div class="form-hint">Leave empty to keep existing key</div>
                </div>
                <div class="form-group">
                    <label for="paystack_webhook_secret_live">Webhook Secret</label>
                    <input type="text" id="paystack_webhook_secret_live" name="webhook_secret_live" 
                           value="<?php echo htmlspecialchars($paystackSettings['webhook_secret_live'] ?? ''); ?>"
                           placeholder="Optional">
                </div>
            </div>
            
            <!-- Webhook URL -->
            <div class="settings-card">
                <div class="section-subtitle">
                    <span>Webhook URL</span>
                </div>
                <div class="form-hint">Copy this URL and add it to your Paystack dashboard (Settings → API Keys & Webhooks):</div>
                <div class="webhook-url-wrap">
                    <input type="text" class="webhook-url" id="paystack-webhook-url" readonly value="<?php echo htmlspecialchars(rtrim(SITE_URL, '/') . '/api/paystack-webhook.php'); ?>">
                    <button type="button" class="webhook-copy-btn" onclick="copyWebhookUrl('paystack-webhook-url', this)">Copy URL</button>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="save-section">
                <button type="submit" class="btn-save">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Paystack Settings
                </button>
            </div>
        </form>
    </div>
    
    <!-- Flutterwave Tab -->
    <div id="tab-flutterwave" class="tab-content">
        <form method="POST" action="">
            <input type="hidden" name="gateway" value="flutterwave">
            
            <div class="settings-card">
                <div class="section-header">
                    <div class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Flutterwave Configuration
                    </div>
                    <span class="status-badge <?php echo !empty($flutterwaveSettings['is_active']) ? 'active' : 'inactive'; ?>">
                        <span class="status-dot"></span>
                        <?php echo !empty($flutterwaveSettings['is_active']) ? 'Active' : 'Inactive'; ?>
                    </span>
                </div>
                
                <!-- Enable/Disable Toggle -->
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-label">Enable Flutterwave</div>
                        <div class="toggle-description">Allow customers to pay with Flutterwave</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_active" <?php echo !empty($flutterwaveSettings['is_active']) ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <!-- Test Mode Toggle -->
                <div class="toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-label">Test Mode</div>
                        <div class="toggle-description">Use test credentials (no real payments)</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="test_mode" id="flutterwave_test_mode" <?php echo !empty($flutterwaveSettings['test_mode']) ? 'checked' : ''; ?> onchange="toggleTestCredentials('flutterwave', this.checked)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            
            <!-- Test Credentials -->
            <div class="settings-card" id="flutterwave_test_credentials" style="display: <?php echo !empty($flutterwaveSettings['test_mode']) ? 'block' : 'none'; ?>;">
                <div class="section-subtitle">
                    <span>Test Credentials</span>
                    <span class="badge badge-test">TEST</span>
                </div>
                <div class="form-group">
                    <label for="flw_public_key_test">Public Key</label>
                    <input type="text" id="flw_public_key_test" name="public_key_test" 
                           value="<?php echo htmlspecialchars($flutterwaveSettings['public_key_test'] ?? ''); ?>"
                           placeholder="FLWPUBK_TEST-xxxxxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="flw_secret_key_test">Secret Key</label>
                    <input type="password" id="flw_secret_key_test" name="secret_key_test" 
                           placeholder="<?php echo !empty($flutterwaveSettings['secret_key_test']) ? '••••••••••••••••' : 'FLWSECK_TEST-xxxxxxxxxxxxx'; ?>">
                    <div class="form-hint">Leave empty to keep existing key</div>
                </div>
                <div class="form-group">
                    <label for="flw_webhook_secret_test">Webhook Secret Hash</label>
                    <input type="text" id="flw_webhook_secret_test" name="webhook_secret_test" 
                           value="<?php echo htmlspecialchars($flutterwaveSettings['webhook_secret_test'] ?? ''); ?>"
                           placeholder="Optional">
                </div>
            </div>
            
            <!-- Live Credentials -->
            <div class="settings-card">
                <div class="section-subtitle">
                    <span>Live Credentials</span>
                    <span class="badge badge-live">LIVE</span>
                </div>
                <div class="form-group">
                    <label for="flw_public_key_live">Public Key</label>
                    <input type="text" id="flw_public_key_live" name="public_key_live" 
                           value="<?php echo htmlspecialchars($flutterwaveSettings['public_key_live'] ?? ''); ?>"
                           placeholder="FLWPUBK-xxxxxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="flw_secret_key_live">Secret Key</label>
                    <input type="password" id="flw_secret_key_live" name="secret_key_live" 
                           placeholder="<?php echo !empty($flutterwaveSettings['secret_key_live']) ? '••••••••••••••••' : 'FLWSECK-xxxxxxxxxxxxx'; ?>">
                    <div class="form-hint">Leave empty to keep existing key</div>
                </div>
                <div class="form-group">
                    <label for="flw_webhook_secret_live">Webhook Secret Hash</label>
                    <input type="text" id="flw_webhook_secret_live" name="webhook_secret_live" 
                           value="<?php echo htmlspecialchars($flutterwaveSettings['webhook_secret_live'] ?? ''); ?>"
                           placeholder="Optional">
                </div>
            </div>
            
            <!-- Webhook URL -->
            <div class="settings-card">
                <div class="section-subtitle">
                    <span>Webhook URL</span>
                </div>
                <div class="form-hint">Copy this URL and add it to your Flutterwave dashboard (Settings → Webhooks):</div>
                <div class="webhook-url-wrap">
                    <input type="text" class="webhook-url" id="flutterwave-webhook-url" readonly value="<?php echo htmlspecialchars(rtrim(SITE_URL, '/') . '/api/flutterwave-webhook.php'); ?>">
                    <button type="button" class="webhook-copy-btn" onclick="copyWebhookUrl('flutterwave-webhook-url', this)">Copy URL</button>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="save-section">
                <button type="submit" class="btn-save">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Flutterwave Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyWebhookUrl(inputId, btn) {
    var input = document.getElementById(inputId);
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
            setTimeout(function() { btn.textContent = 'Copy URL'; btn.classList.remove('copied'); }, 2000);
        });
    }
}
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    
    // Add active class to clicked button
    event.target.closest('.tab-button').classList.add('active');
}

function toggleTestCredentials(gateway, isChecked) {
    const testCredentialsSection = document.getElementById(gateway + '_test_credentials');
    if (testCredentialsSection) {
        testCredentialsSection.style.display = isChecked ? 'block' : 'none';
    }
}

// Initialize visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    // Paystack
    const paystackTestMode = document.getElementById('paystack_test_mode');
    if (paystackTestMode) {
        toggleTestCredentials('paystack', paystackTestMode.checked);
    }
    
    // Flutterwave
    const flutterwaveTestMode = document.getElementById('flutterwave_test_mode');
    if (flutterwaveTestMode) {
        toggleTestCredentials('flutterwave', flutterwaveTestMode.checked);
    }
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
