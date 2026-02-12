<?php
/**
 * Restaurant-branded email templates for orders and reservations
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/order-functions.php';

/**
 * Get restaurant-branded email base wrapper
 *
 * @param array $restaurant Restaurant row (id, name, logo, slug)
 * @param string $title Page title
 * @param string $bodyContent HTML content for the body
 * @return string Full HTML email
 */
function getRestaurantEmailBase($restaurant, $title, $bodyContent) {
    $restaurantId = (int)($restaurant['id'] ?? 0);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $customization = getCustomizationSettings($restaurantId);
    $primaryColor = $customization['primary_color'] ?? '#111111';
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $uploadUrl = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL, '/') : $baseUrl . '/uploads';
    $logoUrl = '';
    if (!empty($restaurant['logo'])) {
        $logoUrl = $uploadUrl . '/logos/' . $restaurant['logo'];
    }

    $headerContent = $logoUrl
        ? '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . $restaurantName . '" style="max-height:48px;max-width:100%;">'
        : '<span style="color:#fff;font-size:20px;font-weight:700;">' . $restaurantName . '</span>';

    return '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title) . '</title>
<style>
body{margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f4f4f5;}
.container{max-width:600px;margin:0 auto;padding:24px;}
.card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);overflow:hidden;}
.header{padding:24px;text-align:center;background:' . htmlspecialchars($primaryColor) . ';}
.content{padding:24px;color:#374151;line-height:1.6;}
.footer{padding:16px;text-align:center;color:#9ca3af;font-size:12px;}
.footer a{color:#6366f1;text-decoration:none;}
table.items{width:100%;border-collapse:collapse;}
table.items td{padding:8px 0;border-bottom:1px solid #f3f4f6;}
table.items td:last-child{text-align:right;font-weight:600;}
.badge{display:inline-block;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;}
</style>
</head>
<body>
<div class="container">
<div class="card">
<div class="header">' . $headerContent . '</div>
<div class="content">' . $bodyContent . '</div>
<div class="footer">
&copy; ' . date('Y') . ' ' . $restaurantName . '. <a href="' . htmlspecialchars($baseUrl) . '">Visit site</a>
</div>
</div>
</div>
</body>
</html>';
}

/**
 * Order confirmation email (customer)
 */
function getOrderConfirmationEmail($order, $orderItems, $restaurant) {
    $orderNum = getOrderDisplayNumber($order);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $customerName = htmlspecialchars($order['customer_name'] ?? '');
    $deliveryAddress = htmlspecialchars($order['delivery_address'] ?? '');
    $currencySymbol = '₦';

    $itemsHtml = '';
    foreach ($orderItems as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $price = (float)($item['price'] ?? 0);
        $lineTotal = $qty * $price;
        $itemsHtml .= '<tr><td>' . htmlspecialchars($item['name'] ?? '') . ' &times; ' . $qty . '</td><td>' . $currencySymbol . number_format($lineTotal, 2) . '</td></tr>';
    }

    $subtotal = (float)($order['subtotal'] ?? 0);
    $deliveryFee = (float)($order['delivery_fee'] ?? 0);
    $tax = (float)($order['tax'] ?? 0);
    $total = (float)($order['total'] ?? 0);

    $body = '<h2 style="margin:0 0 16px;font-size:20px;color:#111827;">Order Confirmation</h2>
<p>Hi ' . $customerName . ',</p>
<p>Thank you for your order at ' . $restaurantName . '. We have received your order and will prepare it shortly.</p>
<p><strong>Order #' . htmlspecialchars($orderNum) . '</strong></p>
<table class="items" style="margin:16px 0;">
<thead><tr><td style="font-weight:600;color:#6b7280;">Item</td><td style="font-weight:600;color:#6b7280;">Total</td></tr></thead>
<tbody>' . $itemsHtml . '</tbody>
</table>
<p style="margin:16px 0;">
<strong>Subtotal:</strong> ' . $currencySymbol . number_format($subtotal, 2) . '<br>
' . ($deliveryFee > 0 ? '<strong>Delivery:</strong> ' . $currencySymbol . number_format($deliveryFee, 2) . '<br>' : '') . '
' . ($tax > 0 ? '<strong>Tax:</strong> ' . $currencySymbol . number_format($tax, 2) . '<br>' : '') . '
<strong>Total:</strong> ' . $currencySymbol . number_format($total, 2) . '
</p>
<p><strong>Delivery address:</strong><br>' . nl2br($deliveryAddress) . '</p>
<p>Status: <span class="badge" style="background:#fef3c7;color:#9a3412;">Pending</span></p>';

    return getRestaurantEmailBase($restaurant, 'Order Confirmation', $body);
}

/**
 * Order status change email (customer)
 */
function getOrderStatusEmail($order, $orderItems, $restaurant, $newStatus) {
    $orderNum = getOrderDisplayNumber($order);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $customerName = htmlspecialchars($order['customer_name'] ?? '');
    $currencySymbol = '₦';

    $statusLabels = [
        'confirmed' => ['text' => 'Confirmed', 'color' => '#059669', 'bg' => '#d1fae5'],
        'on_hold' => ['text' => 'On Hold', 'color' => '#d97706', 'bg' => '#fef3c7'],
        'cancelled' => ['text' => 'Cancelled', 'color' => '#dc2626', 'bg' => '#fee2e2'],
        'completed' => ['text' => 'Completed', 'color' => '#2563eb', 'bg' => '#dbeafe'],
    ];
    $statusInfo = $statusLabels[$newStatus] ?? ['text' => ucfirst($newStatus), 'color' => '#6b7280', 'bg' => '#f3f4f6'];

    $itemsHtml = '';
    foreach ($orderItems as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $price = (float)($item['price'] ?? 0);
        $lineTotal = $qty * $price;
        $itemsHtml .= '<tr><td>' . htmlspecialchars($item['name'] ?? '') . ' &times; ' . $qty . '</td><td>' . $currencySymbol . number_format($lineTotal, 2) . '</td></tr>';
    }

    $total = (float)($order['total'] ?? 0);
    $body = '<h2 style="margin:0 0 16px;font-size:20px;color:#111827;">Order Update</h2>
<p>Hi ' . $customerName . ',</p>
<p>Your order at ' . $restaurantName . ' has been updated.</p>
<p><strong>Order #' . htmlspecialchars($orderNum) . '</strong></p>
<table class="items" style="margin:16px 0;">
<tbody>' . $itemsHtml . '</tbody>
</table>
<p><strong>Total:</strong> ' . $currencySymbol . number_format($total, 2) . '</p>
<p>Status: <span class="badge" style="background:' . $statusInfo['bg'] . ';color:' . $statusInfo['color'] . ';">' . $statusInfo['text'] . '</span></p>';

    return getRestaurantEmailBase($restaurant, 'Order Update', $body);
}

/**
 * Manager new order alert
 */
function getManagerNewOrderEmail($order, $orderItems, $restaurant) {
    $orderNum = getOrderDisplayNumber($order);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $customerName = htmlspecialchars($order['customer_name'] ?? '');
    $customerPhone = htmlspecialchars($order['customer_phone'] ?? '');
    $customerEmail = htmlspecialchars($order['customer_email'] ?? '');
    $deliveryAddress = htmlspecialchars($order['delivery_address'] ?? '');
    $currencySymbol = '₦';

    $itemsHtml = '';
    foreach ($orderItems as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $price = (float)($item['price'] ?? 0);
        $lineTotal = $qty * $price;
        $itemsHtml .= '<tr><td>' . htmlspecialchars($item['name'] ?? '') . ' &times; ' . $qty . '</td><td>' . $currencySymbol . number_format($lineTotal, 2) . '</td></tr>';
    }

    $total = (float)($order['total'] ?? 0);
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $ordersUrl = $baseUrl . '/manager/orders.php?slug=' . urlencode($restaurant['slug'] ?? '');

    $body = '<h2 style="margin:0 0 16px;font-size:20px;color:#111827;">New Order Received</h2>
<p>You have received a new order at ' . $restaurantName . '.</p>
<p><strong>Order #' . htmlspecialchars($orderNum) . '</strong></p>
<p><strong>Customer:</strong> ' . $customerName . '<br>
<strong>Phone:</strong> ' . $customerPhone . '<br>
<strong>Email:</strong> ' . $customerEmail . '</p>
<p><strong>Delivery address:</strong><br>' . nl2br($deliveryAddress) . '</p>
<table class="items" style="margin:16px 0;">
<tbody>' . $itemsHtml . '</tbody>
</table>
<p><strong>Total:</strong> ' . $currencySymbol . number_format($total, 2) . '</p>
<p><a href="' . htmlspecialchars($ordersUrl) . '" style="display:inline-block;background:#111827;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">View Order</a></p>';

    return getRestaurantEmailBase($restaurant, 'New Order', $body);
}

/**
 * Reservation confirmation email (guest)
 */
function getReservationConfirmationEmail($reservation, $restaurant) {
    $resNum = getReservationDisplayNumber($reservation);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $guestName = htmlspecialchars($reservation['guest_name'] ?? '');
    $date = !empty($reservation['reservation_date']) ? date('l, F j, Y', strtotime($reservation['reservation_date'])) : '-';
    $time = !empty($reservation['reservation_time']) ? date('g:i A', strtotime($reservation['reservation_time'])) : '-';
    $partySize = (int)($reservation['party_size'] ?? 1);
    $status = $reservation['status'] ?? 'pending';

    $body = '<h2 style="margin:0 0 16px;font-size:20px;color:#111827;">Reservation Received</h2>
<p>Hi ' . $guestName . ',</p>
<p>Thank you for your reservation at ' . $restaurantName . '. We have received your booking request.</p>
<p><strong>Reservation #' . htmlspecialchars($resNum) . '</strong></p>
<p><strong>Date:</strong> ' . $date . '<br>
<strong>Time:</strong> ' . $time . '<br>
<strong>Party size:</strong> ' . $partySize . '</p>
<p>Status: <span class="badge" style="background:#fef3c7;color:#9a3412;">Pending</span></p>
<p>We will confirm your reservation shortly.</p>';

    return getRestaurantEmailBase($restaurant, 'Reservation Received', $body);
}

/**
 * Reservation status change email (guest)
 */
function getReservationStatusEmail($reservation, $restaurant, $newStatus) {
    $resNum = getReservationDisplayNumber($reservation);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $guestName = htmlspecialchars($reservation['guest_name'] ?? '');
    $date = !empty($reservation['reservation_date']) ? date('l, F j, Y', strtotime($reservation['reservation_date'])) : '-';
    $time = !empty($reservation['reservation_time']) ? date('g:i A', strtotime($reservation['reservation_time'])) : '-';
    $partySize = (int)($reservation['party_size'] ?? 1);

    $statusLabels = [
        'confirmed' => ['text' => 'Confirmed', 'color' => '#059669', 'bg' => '#d1fae5'],
        'rejected' => ['text' => 'Rejected', 'color' => '#dc2626', 'bg' => '#fee2e2'],
        'cancelled' => ['text' => 'Cancelled', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
        'completed' => ['text' => 'Completed', 'color' => '#2563eb', 'bg' => '#dbeafe'],
    ];
    $statusInfo = $statusLabels[$newStatus] ?? ['text' => ucfirst($newStatus), 'color' => '#6b7280', 'bg' => '#f3f4f6'];

    $body = '<h2 style="margin:0 0 16px;font-size:20px;color:#111827;">Reservation Update</h2>
<p>Hi ' . $guestName . ',</p>
<p>Your reservation at ' . $restaurantName . ' has been updated.</p>
<p><strong>Reservation #' . htmlspecialchars($resNum) . '</strong></p>
<p><strong>Date:</strong> ' . $date . '<br>
<strong>Time:</strong> ' . $time . '<br>
<strong>Party size:</strong> ' . $partySize . '</p>
<p>Status: <span class="badge" style="background:' . $statusInfo['bg'] . ';color:' . $statusInfo['color'] . ';">' . $statusInfo['text'] . '</span></p>';

    return getRestaurantEmailBase($restaurant, 'Reservation Update', $body);
}

/**
 * Reservation deposit paid email (guest)
 */
function getReservationDepositPaidEmail($reservation, $restaurant) {
    $resNum = getReservationDisplayNumber($reservation);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $guestName = htmlspecialchars($reservation['guest_name'] ?? '');
    $date = !empty($reservation['reservation_date']) ? date('l, F j, Y', strtotime($reservation['reservation_date'])) : '-';
    $time = !empty($reservation['reservation_time']) ? date('g:i A', strtotime($reservation['reservation_time'])) : '-';
    $partySize = (int)($reservation['party_size'] ?? 1);
    $currencySymbol = '₦';
    $depositAmount = (float)($reservation['deposit_amount'] ?? 0);

    $body = '<h2 style="margin:0 0 16px;font-size:20px;color:#111827;">Reservation Confirmed!</h2>
<p>Hi ' . $guestName . ',</p>
<p>Your deposit has been paid. Your reservation at ' . $restaurantName . ' is now confirmed.</p>
<p><strong>Reservation #' . htmlspecialchars($resNum) . '</strong></p>
<p><strong>Date:</strong> ' . $date . '<br>
<strong>Time:</strong> ' . $time . '<br>
<strong>Party size:</strong> ' . $partySize . '</p>
' . ($depositAmount > 0 ? '<p><strong>Deposit paid:</strong> ' . $currencySymbol . number_format($depositAmount, 2) . '</p>' : '') . '
<p>Status: <span class="badge" style="background:#d1fae5;color:#059669;">Confirmed</span></p>
<p>We look forward to seeing you!</p>';

    return getRestaurantEmailBase($restaurant, 'Reservation Confirmed', $body);
}

/**
 * Manager new reservation alert
 */
function getManagerNewReservationEmail($reservation, $restaurant) {
    $resNum = getReservationDisplayNumber($reservation);
    $restaurantName = htmlspecialchars($restaurant['name'] ?? 'Restaurant');
    $guestName = htmlspecialchars($reservation['guest_name'] ?? '');
    $guestEmail = htmlspecialchars($reservation['guest_email'] ?? '');
    $guestPhone = htmlspecialchars($reservation['guest_phone'] ?? '');
    $date = !empty($reservation['reservation_date']) ? date('l, F j, Y', strtotime($reservation['reservation_date'])) : '-';
    $time = !empty($reservation['reservation_time']) ? date('g:i A', strtotime($reservation['reservation_time'])) : '-';
    $partySize = (int)($reservation['party_size'] ?? 1);
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $reservationsUrl = $baseUrl . '/manager/restaurant-reservations.php?slug=' . urlencode($restaurant['slug'] ?? '');

    $body = '<h2 style="margin:0 0 16px;font-size:20px;color:#111827;">New Reservation Received</h2>
<p>You have received a new table reservation at ' . $restaurantName . '.</p>
<p><strong>Reservation #' . htmlspecialchars($resNum) . '</strong></p>
<p><strong>Guest:</strong> ' . $guestName . '<br>
<strong>Email:</strong> ' . $guestEmail . '<br>
<strong>Phone:</strong> ' . $guestPhone . '</p>
<p><strong>Date:</strong> ' . $date . '<br>
<strong>Time:</strong> ' . $time . '<br>
<strong>Party size:</strong> ' . $partySize . '</p>
<p><a href="' . htmlspecialchars($reservationsUrl) . '" style="display:inline-block;background:#111827;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">View Reservations</a></p>';

    return getRestaurantEmailBase($restaurant, 'New Reservation', $body);
}
