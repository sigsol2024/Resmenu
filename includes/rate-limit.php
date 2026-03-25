<?php
/**
 * Lightweight file-based rate limiter (no DB migration required).
 * Stores per-key timestamp arrays in a writable temp directory.
 */
require_once __DIR__ . '/../config/config.php';

function getRateLimitDir() {
    $candidates = [];
    $tmp = sys_get_temp_dir();
    if (is_string($tmp) && $tmp !== '') {
        $candidates[] = rtrim($tmp, "\\/") . DIRECTORY_SEPARATOR . 'resmenu-rate-limits';
    }
    $candidates[] = rtrim(BASE_PATH, "\\/") . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'rate-limits';
    $candidates[] = rtrim(UPLOAD_PATH, "\\/") . DIRECTORY_SEPARATOR . 'rate-limits';

    foreach ($candidates as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            return $dir;
        }
    }
    return null;
}

function rateLimitKeyToPath($key) {
    $dir = getRateLimitDir();
    if (!$dir) return null;
    $hash = hash('sha256', (string)$key);
    return $dir . DIRECTORY_SEPARATOR . $hash . '.json';
}

function readRateLimitState($key) {
    $path = rateLimitKeyToPath($key);
    if (!$path || !file_exists($path)) return ['hits' => []];
    $raw = @file_get_contents($path);
    if (!is_string($raw) || $raw === '') return ['hits' => []];
    $data = json_decode($raw, true);
    if (!is_array($data) || !is_array($data['hits'] ?? null)) return ['hits' => []];
    return ['hits' => array_values(array_map('intval', $data['hits']))];
}

function writeRateLimitState($key, $state) {
    $path = rateLimitKeyToPath($key);
    if (!$path) return false;
    $payload = json_encode($state);
    if (!is_string($payload)) return false;
    return @file_put_contents($path, $payload, LOCK_EX) !== false;
}

function pruneHits($hits, $windowSeconds) {
    $now = time();
    $cutoff = $now - (int)$windowSeconds;
    $out = [];
    foreach ($hits as $t) {
        $t = (int)$t;
        if ($t >= $cutoff && $t <= ($now + 5)) {
            $out[] = $t;
        }
    }
    sort($out);
    return $out;
}

/**
 * Check rate limit for a key.
 * @return array ['allowed'=>bool,'remaining'=>int,'retry_after'=>int]
 */
function rateLimitCheck($key, $limit, $windowSeconds) {
    $limit = max(1, (int)$limit);
    $windowSeconds = max(1, (int)$windowSeconds);
    $state = readRateLimitState($key);
    $hits = pruneHits($state['hits'] ?? [], $windowSeconds);
    $count = count($hits);
    if ($count < $limit) {
        return ['allowed' => true, 'remaining' => $limit - $count, 'retry_after' => 0];
    }
    $oldest = (int)($hits[0] ?? time());
    $retryAfter = max(1, ($oldest + $windowSeconds) - time());
    return ['allowed' => false, 'remaining' => 0, 'retry_after' => $retryAfter];
}

/**
 * Record a hit for a key.
 */
function rateLimitHit($key, $windowSeconds) {
    $windowSeconds = max(1, (int)$windowSeconds);
    $state = readRateLimitState($key);
    $hits = pruneHits($state['hits'] ?? [], $windowSeconds);
    $hits[] = time();
    $state = ['hits' => $hits];
    return writeRateLimitState($key, $state);
}

/**
 * Seconds since last hit for a key (within the given window).
 * Returns null if no hits exist.
 */
function rateLimitSecondsSinceLastHit($key, $windowSeconds) {
    $windowSeconds = max(1, (int)$windowSeconds);
    $state = readRateLimitState($key);
    $hits = pruneHits($state['hits'] ?? [], $windowSeconds);
    if (empty($hits)) return null;
    $last = (int)end($hits);
    return max(0, time() - $last);
}

/**
 * Enforce a simple cooldown between hits.
 * @return array ['allowed'=>bool,'retry_after'=>int]
 */
function rateLimitCooldownCheck($key, $cooldownSeconds, $windowSeconds) {
    $cooldownSeconds = max(1, (int)$cooldownSeconds);
    $since = rateLimitSecondsSinceLastHit($key, $windowSeconds);
    if ($since === null) return ['allowed' => true, 'retry_after' => 0];
    if ($since >= $cooldownSeconds) return ['allowed' => true, 'retry_after' => 0];
    return ['allowed' => false, 'retry_after' => max(1, $cooldownSeconds - $since)];
}

