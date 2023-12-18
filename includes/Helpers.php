<?php

/**
 * Helper functions.
 */
class Helpers
{

    static $pdo;
    static $config;


    /**
     * Returns configuration options.
     *
     * Includes config-db.php, config-markup.php, config-markup.local.php and sets self::$config.
     *
     * @return array
     */
    public static function getConfig()
    {
        $config = [];

        if (!self::$config) {
            require_once __DIR__ . '/../config/config-db.php';
            require_once __DIR__ . '/../config/config-markup.php';
            if(file_exists(__DIR__ . '/../config/config-markup.local.php'))
                require_once __DIR__ . '/../config/config-markup.local.php';
            self::$config = $config;
        }
        return self::$config;
    }


    /**
     * Returns database connection object and sets self::$pdo.
     *
     * @return PDO
     */
    public static function getDbConnection()
    {

        if (!self::$pdo) {
            $config = self::getConfig();

            $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // connect to database
            $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);

            self::$pdo = $pdo;
        }

        return self::$pdo;
    }

}
