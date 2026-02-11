<?php
/**
 * Expire Bank Transfer Draft
 * Called when countdown finishes - removes the draft. No order is created.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
if (empty($token)) {
    http_response_code(400);
    exit;
}

$pdo = getDBConnection();
if ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM pending_bank_transfers WHERE token = ?");
    $stmt->execute([$token]);
}

http_response_code(200);
echo 'OK';
