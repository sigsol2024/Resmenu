<?php
/**
 * Restaurant Payment Settings Helper Functions
 *
 * Per-restaurant payment configuration for customer checkout (Paystack, Flutterwave, Bank Transfer).
 * Separate from admin payment_settings which handles subscription billing.
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/subscription.php';

/**
 * Get restaurant payment settings for one or all gateways
 *
 * @param int $restaurantId
 * @param string|null $gateway Specific gateway (paystack, flutterwave, bank_transfer) or null for all
 * @return array Single gateway settings or associative array keyed by gateway
 */
function getRestaurantPaymentSettings($restaurantId, $gateway = null) {
    global $pdo;

    if (!$pdo) return $gateway ? [] : [];

    try {
        if ($gateway) {
            $stmt = $pdo->prepare("SELECT * FROM restaurant_payment_settings WHERE restaurant_id = ? AND gateway = ?");
            $stmt->execute([$restaurantId, $gateway]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }

        $stmt = $pdo->prepare("SELECT * FROM restaurant_payment_settings WHERE restaurant_id = ?");
        $stmt->execute([$restaurantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['gateway']] = $row;
        }
        return $result;
    } catch (PDOException $e) {
        error_log("Error getting restaurant payment settings: " . $e->getMessage());
        return $gateway ? [] : [];
    }
}

/**
 * Update or insert restaurant payment settings (upsert)
 *
 * @param int $restaurantId
 * @param string $gateway paystack, flutterwave, or bank_transfer
 * @param array $settings
 * @return bool
 */
function updateRestaurantPaymentSettings($restaurantId, $gateway, $settings) {
    global $pdo;

    if (!$pdo) return false;

    $allowedGateways = ['paystack', 'flutterwave', 'bank_transfer'];
    if (!in_array($gateway, $allowedGateways)) return false;

    try {
        $existing = getRestaurantPaymentSettings($restaurantId, $gateway);

        if ($gateway === 'bank_transfer') {
            $allowedFields = ['is_active', 'bank_name', 'account_number', 'account_name'];
        } else {
            $allowedFields = [
                'is_active', 'test_mode',
                'public_key_test', 'secret_key_test', 'webhook_secret_test',
                'public_key_live', 'secret_key_live', 'webhook_secret_live'
            ];
        }

        $updates = [];
        $params = [];

        foreach ($allowedFields as $field) {
            if (!isset($settings[$field])) continue;

            $value = $settings[$field];
            if (is_bool($value)) $value = $value ? 1 : 0;

            // Encrypt secret keys for paystack/flutterwave
            if (in_array($gateway, ['paystack', 'flutterwave']) && strpos($field, 'secret_key') !== false && !empty($value)) {
                $value = encryptApiKey($value);
            }

            $updates[] = "`{$field}` = ?";
            $params[] = $value;
        }

        if (empty($updates)) return true;

        $params[] = $restaurantId;
        $params[] = $gateway;

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE restaurant_payment_settings SET " . implode(', ', $updates) . " WHERE restaurant_id = ? AND gateway = ?");
            return $stmt->execute($params);
        }

        // Insert new row - build columns from updates
        $insertCols = ['restaurant_id', 'gateway'];
        $insertVals = [$restaurantId, $gateway];
        $updateParts = [];
        foreach ($allowedFields as $f) {
            if (isset($settings[$f])) {
                $insertCols[] = $f;
                $v = $settings[$f];
                if (is_bool($v)) $v = $v ? 1 : 0;
                if (in_array($gateway, ['paystack', 'flutterwave']) && strpos($f, 'secret_key') !== false && !empty($v)) {
                    $v = encryptApiKey($v);
                }
                $insertVals[] = $v;
                $updateParts[] = "`{$f}` = VALUES(`{$f}`)";
            }
        }
        $placeholders = implode(', ', array_fill(0, count($insertCols), '?'));
        $sql = "INSERT INTO restaurant_payment_settings (" . implode(', ', array_map(fn($c) => "`$c`", $insertCols)) . ") VALUES ($placeholders) ON DUPLICATE KEY UPDATE " . implode(', ', $updateParts);
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($insertVals);
    } catch (PDOException $e) {
        error_log("Error updating restaurant payment settings: " . $e->getMessage());
        return false;
    }
}

/**
 * Get only enabled payment methods for a restaurant (for checkout)
 *
 * @param int $restaurantId
 * @return array List of enabled methods with gateway, label, and any display data (e.g. bank details)
 */
function getRestaurantActivePaymentMethods($restaurantId) {
    $all = getRestaurantPaymentSettings($restaurantId);
    $methods = [];

    foreach ($all as $gateway => $row) {
        if (empty($row['is_active'])) continue;

        $label = match ($gateway) {
            'paystack' => 'Pay with Paystack',
            'flutterwave' => 'Pay with Flutterwave',
            'bank_transfer' => 'Bank Transfer',
            default => ucfirst(str_replace('_', ' ', $gateway))
        };

        $methods[] = [
            'gateway' => $gateway,
            'label' => $label,
            'bank_name' => $row['bank_name'] ?? null,
            'account_number' => $row['account_number'] ?? null,
            'account_name' => $row['account_name'] ?? null
        ];
    }

    return $methods;
}

/**
 * Initialize Paystack payment for restaurant order
 *
 * @param int $restaurantId
 * @param array $data amount (in main currency), email, metadata (restaurant_id, order_id, slug)
 * @return array ['success' => bool, 'authorization_url' => string, 'reference' => string, 'error' => string]
 */
function initializeRestaurantPaystackPayment($restaurantId, $data) {
    $keys = getRestaurantGatewayKeys($restaurantId, 'paystack');
    if (empty($keys['secret_key'])) {
        return ['success' => false, 'error' => 'Paystack is not configured'];
    }

    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $meta = $data['metadata'] ?? [];
    $callbackUrl = $baseUrl . '/order-payment-callback.php?gateway=paystack&slug=' . urlencode($meta['slug'] ?? '') . '&order_id=' . (int)($meta['order_id'] ?? 0);

    $payload = [
        'email' => $data['email'],
        'amount' => (int)round((float)($data['amount'] ?? 0) * 100),
        'reference' => 'ORD_' . ($data['order_id'] ?? time()) . '_' . bin2hex(random_bytes(4)),
        'callback_url' => $callbackUrl,
        'metadata' => $data['metadata'] ?? []
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.paystack.co/transaction/initialize',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $keys['secret_key'],
            'Content-Type: application/json'
        ]
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Restaurant Paystack init failed: " . $response);
        return ['success' => false, 'error' => 'Failed to initialize payment'];
    }

    $result = json_decode($response, true);
    if (!empty($result['status']) && !empty($result['data']['authorization_url'])) {
        return [
            'success' => true,
            'authorization_url' => $result['data']['authorization_url'],
            'reference' => $result['data']['reference']
        ];
    }
    return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
}

/**
 * Initialize Flutterwave payment for restaurant order
 *
 * @param int $restaurantId
 * @param array $data amount, email, name, phone, metadata
 * @return array ['success' => bool, 'authorization_url' => string, 'reference' => string, 'error' => string]
 */
function initializeRestaurantFlutterwavePayment($restaurantId, $data) {
    $keys = getRestaurantGatewayKeys($restaurantId, 'flutterwave');
    if (empty($keys['secret_key'])) {
        return ['success' => false, 'error' => 'Flutterwave is not configured'];
    }

    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $meta = $data['metadata'] ?? [];
    $redirectUrl = $baseUrl . '/order-payment-callback.php?gateway=flutterwave&slug=' . urlencode($meta['slug'] ?? '') . '&order_id=' . (int)($meta['order_id'] ?? 0);

    $reference = 'ORD_' . ($data['metadata']['order_id'] ?? time()) . '_' . bin2hex(random_bytes(4));
    $payload = [
        'tx_ref' => $reference,
        'amount' => (float)($data['amount'] ?? 0),
        'currency' => $data['currency'] ?? 'NGN',
        'redirect_url' => $redirectUrl,
        'customer' => [
            'email' => $data['email'] ?? '',
            'name' => $data['name'] ?? '',
            'phonenumber' => $data['phone'] ?? ''
        ],
        'customizations' => [
            'title' => 'Order Payment',
            'description' => 'Restaurant order'
        ],
        'meta' => $data['metadata'] ?? []
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.flutterwave.com/v3/payments',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $keys['secret_key'],
            'Content-Type: application/json'
        ]
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Restaurant Flutterwave init failed: " . $response);
        return ['success' => false, 'error' => 'Failed to initialize payment'];
    }

    $result = json_decode($response, true);
    if (!empty($result['status']) && $result['status'] === 'success' && !empty($result['data']['link'])) {
        return [
            'success' => true,
            'authorization_url' => $result['data']['link'],
            'reference' => $reference
        ];
    }
    return ['success' => false, 'error' => $result['message'] ?? 'Unknown error'];
}

/**
 * Verify Paystack transaction (restaurant order)
 */
function verifyRestaurantPaystackPayment($restaurantId, $reference) {
    $keys = getRestaurantGatewayKeys($restaurantId, 'paystack');
    if (empty($keys['secret_key'])) return ['success' => false];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.paystack.co/transaction/verify/' . urlencode($reference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $keys['secret_key']]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    if (!empty($result['status']) && !empty($result['data']['status']) && $result['data']['status'] === 'success') {
        return [
            'success' => true,
            'metadata' => $result['data']['metadata'] ?? []
        ];
    }
    return ['success' => false];
}

/**
 * Verify Flutterwave transaction (restaurant order)
 */
function verifyRestaurantFlutterwavePayment($restaurantId, $transactionId) {
    $keys = getRestaurantGatewayKeys($restaurantId, 'flutterwave');
    if (empty($keys['secret_key'])) return ['success' => false];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.flutterwave.com/v3/transactions/' . urlencode($transactionId) . '/verify',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $keys['secret_key']]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);

    if (!empty($result['status']) && $result['status'] === 'success' && !empty($result['data']['status']) && $result['data']['status'] === 'successful') {
        return [
            'success' => true,
            'metadata' => $result['data']['meta'] ?? []
        ];
    }
    return ['success' => false];
}

/**
 * Get restaurant gateway API keys (decrypted, for webhook validation)
 *
 * @param int $restaurantId
 * @param string $gateway paystack or flutterwave
 * @return array ['secret_key' => string, 'webhook_secret' => string]
 */
function getRestaurantGatewayKeys($restaurantId, $gateway) {
    $settings = getRestaurantPaymentSettings($restaurantId, $gateway);
    if (!$settings) {
        return ['secret_key' => '', 'webhook_secret' => ''];
    }
    $isTestMode = (bool)($settings['test_mode'] ?? true);
    if ($isTestMode) {
        return [
            'secret_key' => decryptApiKey($settings['secret_key_test'] ?? ''),
            'webhook_secret' => $settings['webhook_secret_test'] ?? ''
        ];
    }
    return [
        'secret_key' => decryptApiKey($settings['secret_key_live'] ?? ''),
        'webhook_secret' => $settings['webhook_secret_live'] ?? ''
    ];
}
