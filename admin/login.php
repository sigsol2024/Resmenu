<?php
/**
 * Admin Login Page - Redirect to root (/) where login is served
 */

// Canonical login is at root; redirect so old bookmarks still work
$query = !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
header('Location: /' . $query);
exit;
