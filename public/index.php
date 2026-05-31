<?php

declare(strict_types=1);

/*
 * CodeIgniter 4 front controller (document root: public/)
 */

if (version_compare(PHP_VERSION, '8.1', '<')) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    printf(
        'Your PHP version is %s, but CodeIgniter 4 requires PHP 8.1 or higher.',
        PHP_VERSION,
    );

    exit(1);
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();

require $paths->systemDirectory . '/Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));
