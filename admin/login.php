<?php
/**
 * Admin Login Page - Redirect to root (/) where login is served
 */

// Canonical login is at root; redirect so old bookmarks still work
header('Location: /');
exit;
