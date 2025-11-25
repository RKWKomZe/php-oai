<?php

namespace RKW\OaiConnector\Utility;

use PDO;
use PDOException;

/**
 * DbConnection
 *
 * Handles database connections using PDO and ensures a shared connection instance.
 */
class DbConnection
{
    /**
     * pdo
     * Holds a shared PDO database connection instance
     *
     * @var \PDO|null
     */
    protected static ?PDO $pdo = null;

    /**
     * Returns a shared PDO connection using values from ConfigLoader.
     *
     * @return PDO
     * @throws \RuntimeException on connection failure
     */
    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = ConfigLoader::load();
        $db = $config['database'] ?? null;

        if (!$db || !isset($db['host'], $db['name'], $db['user'], $db['password'])) {
            throw new \RuntimeException('Invalid or missing database configuration.');
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['name']
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $db['user'],
                $db['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            return self::$pdo;
        } catch (PDOException $e) {
            throw new \RuntimeException('Failed to connect to database: ' . $e->getMessage(), 0, $e);
        }
    }

}
