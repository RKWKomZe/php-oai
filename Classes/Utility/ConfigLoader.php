<?php

namespace RKW\OaiConnector\Utility;

use mysqli;

/**
 * ConfigLoader
 *
 * Provides functionality to load configuration data and establish a database connection.
 */
class ConfigLoader
{

    /**
     * config
     * Holds the static configuration array
     *
     * @var array|null
     */
    private static ?array $config = null;


    /**
     * Loads the configuration from the config.php file if not already loaded
     *
     * @return array<string,mixed> Returns the configuration array
     */
    public static function load(): array
    {
        if (self::$config === null) {
            $basePath = dirname(__DIR__, 2);
            self::$config = require $basePath . '/config/config.php';
        }
        return self::$config;
    }

    /**
     * Establishes and returns a MySQLi database connection
     *
     * @return \mysqli Returns a MySQLi connection instance
     *
     * @throws \RuntimeException If the database connection fails
     */
    public static function getDatabaseConnection(): \mysqli
    {
        $config = self::load();

        $db = $config['database'] ?? [];
        $mysqli = new \mysqli(
            $db['host'],
            $db['user'],
            $db['password'],
            $db['name']
        );

        if ($mysqli->connect_error) {
            throw new \RuntimeException('Database connection failed: ' . $mysqli->connect_error);
        }

        return $mysqli;
    }

}
