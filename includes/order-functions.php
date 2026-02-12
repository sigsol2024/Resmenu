<?php
/**
 * Order-related helper functions
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/restaurant-payment-functions.php';

/**
 * Generate a unique 8-character alphanumeric order number
 * @return string
 */
function generateOrderNumber() {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $len = strlen($chars);
    $result = '';
    for ($i = 0; $i < 8; $i++) {
        $result .= $chars[random_int(0, $len - 1)];
    }
    return $result;
}

/**
 * Generate a unique 8-character alphanumeric reservation number (same pattern as orders)
 * @return string
 */
function generateReservationNumber() {
    return generateOrderNumber();
}

/**
 * Get display reservation number (8-char alphanumeric). Uses reservation_number if set, else generates from id for legacy.
 * @param array $reservation Reservation row with id and optionally reservation_number
 * @return string
 */
function getReservationDisplayNumber($reservation) {
    if (!empty($reservation['reservation_number'])) {
        return $reservation['reservation_number'];
    }
    $id = (int)($reservation['id'] ?? 0);
    return strtoupper(str_pad(base_convert((string)$id, 10, 36), 8, '0', STR_PAD_LEFT));
}

/**
 * Get display order number (8-char alphanumeric). Uses order_number if set, else generates from id for legacy orders.
 * @param array $order Order row with id and optionally order_number
 * @return string
 */
function getOrderDisplayNumber($order) {
    if (!empty($order['order_number'])) {
        return $order['order_number'];
    }
    $id = (int)($order['id'] ?? 0);
    return strtoupper(str_pad(base_convert((string)$id, 10, 36), 8, '0', STR_PAD_LEFT));
}

/**
 * Create an order from cart data
 * @param int $restaurantId
 * @param array $cart Items with id, name, price, quantity
 * @param array $customer customer_name, customer_phone, customer_email, delivery_address, payment_method (optional)
 * @param float $deliveryFee
 * @param float $taxRate 0-1 (e.g. 0.08875 for 8.875%)
 * @return array ['success' => bool, 'order_id' => int|null, 'errors' => array]
 */
function createOrder($restaurantId, $cart, $customer, $deliveryFee = 0, $taxRate = 0) {
    $errors = [];
    $pdo = getDBConnection();
    if (!$pdo) {
        return ['success' => false, 'order_id' => null, 'errors' => ['Database connection failed.']];
    }

    $restaurantId = (int) $restaurantId;
    $customerName = trim($customer['customer_name'] ?? '');
    $customerPhone = trim($customer['customer_phone'] ?? '');
    $customerEmail = trim($customer['customer_email'] ?? '');
    $deliveryAddress = trim($customer['delivery_address'] ?? '');
    $paymentMethod = trim($customer['payment_method'] ?? '');

    $activeMethods = getRestaurantActivePaymentMethods($restaurantId);
    $allowedGateways = array_column($activeMethods, 'gateway');
    if (!empty($allowedGateways) && $paymentMethod && !in_array($paymentMethod, $allowedGateways)) {
        $errors[] = 'Invalid payment method.';
    }

    if (empty($customerName)) $errors[] = 'Full name is required.';
    if (empty($customerPhone)) $errors[] = 'Phone number is required.';
    if (empty($customerEmail)) $errors[] = 'Email address is required.';
    if (!isValidEmail($customerEmail)) $errors[] = 'Invalid email address.';
    if (empty($deliveryAddress)) $errors[] = 'Delivery address is required.';
    if (empty($cart)) $errors[] = 'Cart is empty.';

    if (!empty($errors)) {
        return ['success' => false, 'order_id' => null, 'errors' => $errors];
    }

    $subtotal = 0;
    foreach ($cart as $item) {
        $price = (float) ($item['price'] ?? 0);
        $qty = max(1, (int) ($item['quantity'] ?? 1));
        $subtotal += $price * $qty;
    }
    $tax = $subtotal * (float) $taxRate;
    $total = $subtotal + (float) $deliveryFee + $tax;

    try {
        $pdo->beginTransaction();

        $orderNumber = generateOrderNumber();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (restaurant_id, order_number, customer_name, customer_phone, customer_email, delivery_address, payment_method, status, subtotal, delivery_fee, tax, total) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)");
            $stmt->execute([$restaurantId, $orderNumber, $customerName, $customerPhone, $customerEmail, $deliveryAddress, $paymentMethod ?: null, $subtotal, $deliveryFee, $tax, $total]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'order_number') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                $stmt = $pdo->prepare("INSERT INTO orders (restaurant_id, customer_name, customer_phone, customer_email, delivery_address, payment_method, status, subtotal, delivery_fee, tax, total) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)");
                $stmt->execute([$restaurantId, $customerName, $customerPhone, $customerEmail, $deliveryAddress, $paymentMethod ?: null, $subtotal, $deliveryFee, $tax, $total]);
            } else {
                throw $e;
            }
        }
        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        foreach ($cart as $item) {
            $menuItemId = (int) ($item['id'] ?? 0);
            $name = trim($item['name'] ?? '');
            $price = (float) ($item['price'] ?? 0);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            if ($menuItemId && $name && $price > 0) {
                $itemStmt->execute([$orderId, $menuItemId, $name, $price, $quantity]);
            }
        }

        $pdo->commit();
        return ['success' => true, 'order_id' => $orderId, 'errors' => []];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return ['success' => false, 'order_id' => null, 'errors' => ['Unable to create order. Please try again.']];
    }
}

/**
 * Complete reservation deposit from pending online payment (Paystack/Flutterwave success)
 * @param string $reference Pending record reference
 * @param string $gateway paystack or flutterwave
 * @return array ['success' => bool, 'reservation_id' => int|null, 'slug' => string, 'errors' => array]
 */
function completeReservationDepositFromPending($reference, $gateway) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return ['success' => false, 'reservation_id' => null, 'slug' => '', 'errors' => ['Database connection failed.']];
    }

    $stmt = $pdo->prepare("SELECT * FROM pending_online_payments WHERE reference = ? AND gateway = ? AND payment_type = 'reservation' AND reservation_id IS NOT NULL");
    $stmt->execute([$reference, $gateway]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$draft) {
        return ['success' => false, 'reservation_id' => null, 'slug' => '', 'errors' => ['Pending reservation payment not found.']];
    }

    $reservationId = (int)$draft['reservation_id'];
    $restaurantId = (int)$draft['restaurant_id'];

    $pdo->prepare("UPDATE table_reservations SET deposit_paid = 1, status = 'confirmed', updated_at = NOW() WHERE id = ? AND restaurant_id = ?")->execute([$reservationId, $restaurantId]);
    $pdo->prepare("DELETE FROM pending_online_payments WHERE reference = ?")->execute([$reference]);

    $restaurant = getRestaurant($restaurantId);
    $slug = $restaurant['slug'] ?? '';

    return ['success' => true, 'reservation_id' => $reservationId, 'slug' => $slug, 'errors' => []];
}

/**
 * Create order from pending online payment (Paystack/Flutterwave success)
 * @param string $reference Pending record reference
 * @param string $gateway paystack or flutterwave
 * @return array ['success' => bool, 'order_id' => int|null, 'slug' => string, 'type' => 'order'|'reservation', 'reservation_id' => int|null, 'errors' => array]
 */
function createOrderFromPendingOnlinePayment($reference, $gateway) {
    $pdo = getDBConnection();
    if (!$pdo) {
        return ['success' => false, 'order_id' => null, 'slug' => '', 'type' => 'order', 'errors' => ['Database connection failed.']];
    }

    $stmt = $pdo->prepare("SELECT * FROM pending_online_payments WHERE reference = ? AND gateway = ?");
    $stmt->execute([$reference, $gateway]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$draft) {
        return ['success' => false, 'order_id' => null, 'slug' => '', 'type' => 'order', 'errors' => ['Pending payment not found.']];
    }

    if (($draft['payment_type'] ?? 'order') === 'reservation' && !empty($draft['reservation_id'])) {
        $result = completeReservationDepositFromPending($reference, $gateway);
        return [
            'success' => $result['success'],
            'order_id' => null,
            'slug' => $result['slug'],
            'type' => 'reservation',
            'reservation_id' => $result['reservation_id'] ?? null,
            'errors' => $result['errors'] ?? []
        ];
    }

    $cart = json_decode($draft['cart_json'] ?? '[]', true);
    if (!is_array($cart)) $cart = [];

    $subtotal = (float)$draft['subtotal'];
    $taxRate = $subtotal > 0 ? (float)$draft['tax'] / $subtotal : 0;

    $result = createOrder($draft['restaurant_id'], $cart, [
        'customer_name' => $draft['customer_name'],
        'customer_phone' => $draft['customer_phone'],
        'customer_email' => $draft['customer_email'],
        'delivery_address' => $draft['delivery_address'],
        'payment_method' => $gateway,
    ], (float)$draft['delivery_fee'], $taxRate);

    if (!$result['success']) {
        return ['success' => false, 'order_id' => null, 'slug' => '', 'errors' => $result['errors'] ?? []];
    }

    $orderId = (int)$result['order_id'];
    $restaurantId = (int)$draft['restaurant_id'];

    // Payment already succeeded - confirm the order
    $pdo->prepare("UPDATE orders SET status = 'confirmed', updated_at = NOW() WHERE id = ? AND restaurant_id = ?")->execute([$orderId, $restaurantId]);

    $restaurant = getRestaurant($restaurantId);
    $slug = $restaurant['slug'] ?? '';

    $pdo->prepare("DELETE FROM pending_online_payments WHERE reference = ?")->execute([$reference]);

    return ['success' => true, 'order_id' => $orderId, 'slug' => $slug, 'type' => 'order', 'errors' => []];
}
