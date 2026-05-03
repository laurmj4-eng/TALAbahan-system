<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'sql206.infinityfree.com',
        'username'     => 'if0_41764652',
        'password'     => 'yEEY6EnLGIFdD',
        'database'     => 'if0_41764652_mj_chatbot',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'    => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
            return;
        }

        // Environment Detection Logic
        $isLocal = false;
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            // STRICT LOCAL DETECTION: Only use local settings if explicitly on localhost or 127.0.0.1
            if (strpos($host, 'localhost') !== false || $host === '127.0.0.1') {
                $isLocal = true;
            }
        }

        if ($isLocal) {
            // Switch to LOCAL (XAMPP) settings
            $this->default['hostname'] = env('database.local.hostname', 'localhost');
            $this->default['database'] = env('database.local.database', 'mj_chatbot');
            $this->default['username'] = env('database.local.username', 'root');
            $this->default['password'] = env('database.local.password', '');
        } else {
            // FORCED LIVE SETTINGS (InfinityFree)
            // We use the hardcoded defaults directly to avoid any .env issues on free hosting
            $this->default['hostname'] = 'sql206.infinityfree.com';
            $this->default['database'] = 'if0_41764652_mj_chatbot';
            $this->default['username'] = 'if0_41764652';
            $this->default['password'] = 'yEEY6EnLGIfdD';
        }
    }
}
