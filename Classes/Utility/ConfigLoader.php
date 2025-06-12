<?php

namespace RKW\OaiConnector\Utility;

use mysqli;

class ConfigLoader
{
    private static ?array $config = null;

    public static function load(): array
    {
        if (self::$config === null) {
            $basePath = dirname(__DIR__, 2);
            self::$config = require $basePath . '/config/config.php';
        }
        return self::$config;
    }

    public static function getDatabaseConnection(): mysqli
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
