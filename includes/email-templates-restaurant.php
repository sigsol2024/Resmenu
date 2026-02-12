<?php
/**
 * Restaurant-branded email templates for orders and reservations
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/order-functions.php';

/**
 * Get restaurant-branded email base wrapper
 * Design matches admin dashboard style: clean card, header with primary accent, dynamic footer
 *
 * @param array $restaurant Restaurant row (id, name, logo, slug, address, phone, email, website)
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
        ? '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . $restaurantName . '" style="max-height:52px;max-width:100%;display:block;margin:0 auto;">'
        : '<div style="display:flex;align-items:center;justify-content:center;gap:12px;"><div style="width:44px;height:44px;background:' . htmlspecialchars($primaryColor) . ';border-radius:50%;display:flex;align-items:center;justify-content:center;"><span style="color:#fff;font-size:20px;">&#9776;</span></div><h1 style="color:#fff;font-size:24px;font-weight:700;margin:0;font-family:Inter,sans-serif;">' . $restaurantName . '</h1></div>';

    $restaurantAddress = trim($restaurant['address'] ?? '');
    $restaurantPhone = trim($restaurant['phone'] ?? '');
    $restaurantEmail = trim($restaurant['email'] ?? '');
    $restaurantWebsite = trim($restaurant['website'] ?? '');
    $menuUrl = $baseUrl . '/menu/' . urlencode($restaurant['slug'] ?? '');

    $footerLines = [];
    if ($restaurantAddress) $footerLines[] = htmlspecialchars($restaurantAddress);
    if ($restaurantPhone) $footerLines[] = 'Call: ' . htmlspecialchars($restaurantPhone);
    if ($restaurantEmail) $footerLines[] = htmlspecialchars($restaurantEmail);
    $footerText = implode(' | ', $footerLines);
    $footerLinks = '<a href="' . htmlspecialchars($menuUrl) . '" style="color:' . htmlspecialchars($primaryColor) . ';text-decoration:none;">View Menu</a>';
    if ($restaurantWebsite) $footerLinks .= ' &bull; <a href="' . htmlspecialchars($restaurantWebsite) . '" style="color:' . htmlspecialchars($primaryColor) . ';text-decoration:none;">Website</a>';

    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . htmlspecialchars($title) . '</title>
<style>body{margin:0;padding:0;font-family:Inter,-apple-system,sans-serif;background:#f8f5f5;}</style>
</head>
<body style="margin:0;padding:0;font-family:Inter,-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;background:#f8f5f5;">
<div style="max-width:640px;margin:0 auto;padding:24px 16px;">
<div style="background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);overflow:hidden;border:1px solid #e5e7eb;">
<header style="background:#1f2937;padding:28px 24px;text-align:center;border-bottom:4px solid ' . htmlspecialchars($primaryColor) . ';">
' . $headerContent . '
</header>
<section style="padding:32px 28px;color:#374151;line-height:1.6;font-size:15px;">
' . $bodyContent . '
</section>
<footer style="background:#1f2937;padding:24px 28px;text-align:center;">
<p style="color:#9ca3af;font-size:12px;margin:0 0 8px;">' . $restaurantName . '</p>
' . ($footerText ? '<p style="color:#9ca3af;font-size:11px;margin:0 0 8px;">' . $footerText . '</p>' : '') . '
<p style="color:#9ca3af;font-size:11px;margin:0;">' . $footerLinks . ' &bull; &copy; ' . date('Y') . ' ' . $restaurantName . '</p>
</footer>
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
    $customization = getCustomizationSettings((int)($restaurant['id'] ?? 0));
    $primaryColor = $customization['primary_color'] ?? '#111111';
    $orderDate = !empty($order['created_at']) ? date('M j, Y', strtotime($order['created_at'])) : date('M j, Y');

    $itemsHtml = '';
    foreach ($orderItems as $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $price = (float)($item['price'] ?? 0);
        $lineTotal = $qty * $price;
        $itemsHtml .= '<tr><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;"><p style="margin:0;font-weight:600;color:#111827;">' . htmlspecialchars($item['name'] ?? '') . '</p><p style="margin:2px 0 0;font-size:12px;color:#6b7280;">Qty: ' . $qty . '</p></td><td style="padding:12px 0;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:600;color:#111827;">' . $currencySymbol . number_format($lineTotal, 2) . '</td></tr>';
    }

    $subtotal = (float)($order['subtotal'] ?? 0);
    $deliveryFee = (float)($order['delivery_fee'] ?? 0);
    $tax = (float)($order['tax'] ?? 0);
    $total = (float)($order['total'] ?? 0);

    $body = '<h2 style="margin:0 0 8px;font-size:28px;font-weight:700;color:#111827;text-align:center;">Thank you for your order!</h2>
<p style="margin:0 0 24px;font-size:16px;color:#6b7280;text-align:center;">Hello ' . $customerName . ', we\'re preparing your meal. Your order has been confirmed.</p>
<div style="border-bottom:1px solid #e5e7eb;padding-bottom:16px;margin-bottom:20px;">
<h3 style="margin:0 0 4px;font-size:18px;font-weight:600;color:#111827;">Order Summary</h3>
<p style="margin:0;font-size:13px;color:#6b7280;">Order #' . htmlspecialchars($orderNum) . ' &bull; ' . $orderDate . '</p>
</div>
<table style="width:100%;border-collapse:collapse;">
<thead><tr><th style="padding:8px 0;text-align:left;font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;">Item</th><th style="padding:8px 0;text-align:right;font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;">Total</th></tr></thead>
<tbody>' . $itemsHtml . '</tbody>
</table>
<div style="margin-top:24px;padding:20px;background:#f9fafb;border-radius:8px;text-align:right;">
<div style="margin-bottom:8px;display:flex;justify-content:space-between;color:#6b7280;"><span>Subtotal</span><span>' . $currencySymbol . number_format($subtotal, 2) . '</span></div>
' . ($deliveryFee > 0 ? '<div style="margin-bottom:8px;display:flex;justify-content:space-between;color:#6b7280;"><span>Delivery Fee</span><span>' . $currencySymbol . number_format($deliveryFee, 2) . '</span></div>' : '') . '
' . ($tax > 0 ? '<div style="margin-bottom:8px;display:flex;justify-content:space-between;color:#6b7280;"><span>Tax</span><span>' . $currencySymbol . number_format($tax, 2) . '</span></div>' : '') . '
<div style="margin-top:16px;padding-top:16px;border-top:2px solid #e5e7eb;">
<div style="display:flex;justify-content:space-between;align-items:center;padding:16px;background:' . $primaryColor . ';border-radius:8px;color:#fff;">
<span style="font-weight:600;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;">Total Paid</span>
<span style="font-size:22px;font-weight:700;">' . $currencySymbol . number_format($total, 2) . '</span>
</div>
</div>
</div>
<div style="margin-top:24px;padding:16px;background:#f9fafb;border-radius:8px;">
<h4 style="margin:0 0 8px;font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.1em;">Delivery Address</h4>
<p style="margin:0;color:#374151;line-height:1.6;">' . nl2br(htmlspecialchars($deliveryAddress)) . '</p>
</div>
<p style="margin:16px 0 0;"><span style="display:inline-block;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:600;background:#fef3c7;color:#9a3412;">Pending</span></p>';

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
        $itemsHtml .= '<tr><td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">' . htmlspecialchars($item['name'] ?? '') . ' &times; ' . $qty . '</td><td style="padding:10px 0;border-bottom:1px solid #f3f4f6;text-align:right;font-weight:600;">' . $currencySymbol . number_format($lineTotal, 2) . '</td></tr>';
    }

    $total = (float)($order['total'] ?? 0);
    $baseUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : '';
    $ordersUrl = $baseUrl . '/manager/orders.php?slug=' . urlencode($restaurant['slug'] ?? '');
    $customization = getCustomizationSettings((int)($restaurant['id'] ?? 0));
    $primaryColor = $customization['primary_color'] ?? '#111111';

    $body = '<h2 style="margin:0 0 16px;font-size:22px;font-weight:700;color:#111827;">New Order Received</h2>
<p>You have received a new order at ' . $restaurantName . '.</p>
<p><strong>Order #' . htmlspecialchars($orderNum) . '</strong></p>
<p><strong>Customer:</strong> ' . htmlspecialchars($customerName) . '<br>
<strong>Phone:</strong> ' . htmlspecialchars($customerPhone) . '<br>
<strong>Email:</strong> ' . htmlspecialchars($customerEmail) . '</p>
<p><strong>Delivery address:</strong><br>' . nl2br(htmlspecialchars($deliveryAddress)) . '</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;">
<tbody>' . $itemsHtml . '</tbody>
</table>
<p><strong>Total:</strong> ' . $currencySymbol . number_format($total, 2) . '</p>
<p><a href="' . htmlspecialchars($ordersUrl) . '" style="display:inline-block;background:' . htmlspecialchars($primaryColor) . ';color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">View Order</a></p>';

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
    $primaryColor = getCustomizationSettings((int)($restaurant['id'] ?? 0))['primary_color'] ?? '#111111';
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
<p><a href="' . htmlspecialchars($reservationsUrl) . '" style="display:inline-block;background:' . htmlspecialchars($primaryColor) . ';color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">View Reservations</a></p>';

    return getRestaurantEmailBase($restaurant, 'New Reservation', $body);
}
