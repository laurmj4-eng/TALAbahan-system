<?php
$uri = urldecode(
    parse_url('http://localhost' . $_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '',
);

$path = __DIR__ . DIRECTORY_SEPARATOR . ltrim($uri, '/');

if ($uri !== '/' && is_file($path)) {
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'map' => 'application/json',
    ];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile($path);
        return;
    }
    return false;
}

require_once __DIR__ . '/index.php';
