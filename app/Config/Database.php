<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 *
 * - testing:     in-memory SQLite (PHPUnit)
 * - development: fixed XAMPP defaults (localhost / mj_chatbot / root)
 * - production:  credentials from environment (Render dashboard / .env secrets)
 */
class Database extends Config
{
    /**
     * Local XAMPP defaults — only applied when CI_ENVIRONMENT is development.
     */
    private const LOCAL_HOSTNAME = 'localhost';
    private const LOCAL_DATABASE = 'mj_chatbot';
    private const LOCAL_USERNAME = 'root';
    private const LOCAL_PASSWORD = '';

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
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
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

        // Check if database environment variables are set
        $hasEnvDb = getenv('database.default.hostname') !== false 
                 || getenv('DB_HOST') !== false 
                 || getenv('DB_HOSTNAME') !== false;

        if (ENVIRONMENT === 'production' || $hasEnvDb) {
            $this->applyProductionCredentials();
            
            // Keep error reporting active if we are debugging in development mode
            if (ENVIRONMENT === 'development') {
                $this->default['DBDebug'] = isset($_ENV['CI_DEBUG']) ? filter_var($_ENV['CI_DEBUG'], FILTER_VALIDATE_BOOL) : false;
            }

            return;
        }

        // development and any other non-production local runtimes (XAMPP)
        $this->applyLocalDevelopmentCredentials();
    }

    /**
     * XAMPP / local defaults — not read from production env vars.
     */
    private function applyLocalDevelopmentCredentials(): void
    {
        $this->default['hostname'] = self::LOCAL_HOSTNAME;
        $this->default['database'] = self::LOCAL_DATABASE;
        $this->default['username'] = self::LOCAL_USERNAME;
        $this->default['password'] = self::LOCAL_PASSWORD;
        $this->default['port']     = 3306;
        $this->default['DBDebug']  = isset($_ENV['CI_DEBUG']) ? filter_var($_ENV['CI_DEBUG'], FILTER_VALIDATE_BOOL) : false;
    }

    /**
     * Render / production — from CI4 dotted keys or DB_* aliases via getenv().
     */
    private function applyProductionCredentials(): void
    {
        $this->default['hostname'] = $this->envString(
            'database.default.hostname',
            'DB_HOSTNAME',
            'DB_HOST',
        );
        $this->default['database'] = $this->envString(
            'database.default.database',
            'DB_NAME',
            'DB_DATABASE',
        );
        $this->default['username'] = $this->envString(
            'database.default.username',
            'DB_USERNAME',
            'DB_USER',
        );
        $this->default['password'] = $this->envString(
            'database.default.password',
            'DB_PASSWORD',
            'DB_PASS',
        );
        $this->default['port'] = (int) $this->envString(
            'database.default.port',
            'DB_PORT',
        ) ?: 3306;
        $this->default['DBDebug'] = false;
        $this->default['encrypt'] = filter_var(
            $this->envString('database.default.encrypt', 'DB_ENCRYPT') ?: 'false',
            FILTER_VALIDATE_BOOL,
        );
    }

    /**
     * Read the first set value from getenv() (empty string is valid, e.g. XAMPP root password).
     */
    private function envString(string ...$keys): string
    {
        foreach ($keys as $key) {
            $value = getenv($key);

            if ($value !== false) {
                return $value;
            }
        }

        return '';
    }
}
