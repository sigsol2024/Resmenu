<?php
/**
 * Admin AJAX: Search restaurants by name or slug (for template private assignment)
 */

require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode([]);
    exit;
}

$term = '%' . $q . '%';
try {
    $stmt = $pdo->prepare("SELECT id, name, slug FROM restaurants WHERE name LIKE ? OR slug LIKE ? ORDER BY name ASC LIMIT 20");
    $stmt->execute([$term, $term]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $list = array_map(function ($r) {
        return ['id' => (int) $r['id'], 'name' => $r['name'], 'slug' => $r['slug'] ?? ''];
    }, $rows);
    echo json_encode($list);
} catch (PDOException $e) {
    error_log('search-restaurants-ajax: ' . $e->getMessage());
    echo json_encode([]);
}
