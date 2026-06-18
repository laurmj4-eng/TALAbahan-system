<?php
$uri = urldecode(
    parse_url('http://localhost' . $_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '',
);

$path = __DIR__ . DIRECTORY_SEPARATOR . ltrim($uri, '/');

if ($uri !== '/' && is_file($path)) {
    return false;
}

require_once __DIR__ . '/index.php';
