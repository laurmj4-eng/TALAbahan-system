<?php
/**
 * Database Keep-Alive Script
 *
 * Prevents Aiven (or other cloud databases) from auto-shutting down
 * due to inactivity. Safe to commit — contains zero hardcoded credentials.
 *
 * Usage:
 *   php keep_alive.php
 *
 * Schedule via Windows Task Scheduler (every 10 min):
 *   Program/script: php
 *   Arguments:      C:\path\to\keep_alive.php
 */

// ---------------------------------------------------------------------------
// 1. Detect CodeIgniter 4 and bootstrap it (loads .env automatically)
// ---------------------------------------------------------------------------
if (file_exists(__DIR__ . '/spark') || file_exists(__DIR__ . '/app/Config/Database.php')) {
    // Minimal CI4 bootstrap — enough to populate $_ENV / getenv()
    require_once __DIR__ . '/app/Config/Paths.php';
    $paths = new Config\Paths();
    require_once $paths->systemDirectory . '/common.php';

    // Force-load the .env if CodeIgniter didn't already
    $dotenvPath = __DIR__ . '/.env';
    if (file_exists($dotenvPath) && ! getenv('database.default.hostname')) {
        $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if ( ! getenv($key)) {
                putenv("{$key}={$value}");
            }
        }
    }
}

// ---------------------------------------------------------------------------
// 2. Manual .env loader (standalone / non-framework projects)
// ---------------------------------------------------------------------------
function load_dotenv(string $path): void
{
    if ( ! file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if ( ! getenv($key)) {
            putenv("{$key}={$value}");
        }
    }
}

// Load project root .env (if not already loaded by CI4)
if ( ! getenv('database.default.hostname')) {
    load_dotenv(__DIR__ . '/.env');
}

// Also check a local .env.example or .env.production for alternate envs
foreach (['.env.production', '.env.local'] as $alt) {
    $altPath = __DIR__ . '/' . $alt;
    if (file_exists($altPath)) {
        load_dotenv($altPath);
        break; // first found alt wins
    }
}

// ---------------------------------------------------------------------------
// 3. Resolve credentials (CI4 keys → generic keys → defaults)
// ---------------------------------------------------------------------------
function env(string $key, string $default = ''): string
{
    $val = getenv($key);
    return ($val !== false && $val !== '') ? $val : $default;
}

$host = env('database.default.hostname')
     ?: env('DB_HOST',        'localhost');
$db   = env('database.default.database')
     ?: env('DB_NAME',        'mj_chatbot');
$user = env('database.default.username')
     ?: env('DB_USER',        'root');
$pass = env('database.default.password')
     ?: env('DB_PASS',        '');
$port = env('database.default.port')
     ?: env('DB_PORT',        '3306');

// Validate that at least a host and user were resolved
if ($host === '' || $user === '') {
    fprintf(STDERR, "[%s] ERROR: Missing DB_HOST or DB_USER. Check your .env file.\n", date('Y-m-d H:i:s'));
    exit(1);
}

// ---------------------------------------------------------------------------
// 4. Connect and ping
// ---------------------------------------------------------------------------
try {
    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        // SSL negotiation — required by Aiven, harmless for local MySQL
        PDO::MYSQL_ATTR_SSL_CA                 => null,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $opts);
    $pdo->query('SELECT 1');

    echo '[' . date('Y-m-d H:i:s') . "] DB keep-alive OK — {$host}:{$port}/{$db}\n";
} catch (PDOException $e) {
    fprintf(STDERR, "[%s] DB keep-alive FAILED: %s\n", date('Y-m-d H:i:s'), $e->getMessage());
    exit(1);
}
