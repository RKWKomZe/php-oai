<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Factory;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Central Monolog factory for the OAI application.
 * - Creates rotating app.log (all levels)
 * - Creates persistent error.log (only >= ERROR)
 * - Provides static access via LoggerFactory::get()
 */
final class LoggerFactory
{

    private static ?LoggerInterface $instance = null;

    /**
     * Initialize the global logger instance.
     *
     * @param string $logDir  Absolute path to your log directory (e.g. /var/log)
     * @param string $channel Optional channel name (default: oai-app)
     */
    public static function init(string $logDir, string $channel = 'oai-app'): void
    {
        // Ensure directory exists
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Create base logger
        $logger = new Logger($channel);

        // 1) error.log (ERROR and above, separate file)
        $errorHandler = new StreamHandler(
            rtrim($logDir, '/') . '/error.log',
            Level::Error,
            false,
            0664
        );

        // 2) Rotating app.log (INFO and above, keep 7 days)
        $appHandler = new RotatingFileHandler(
            rtrim($logDir, '/') . '/app.log',
            7,                 // keep last 7 files
            Level::Info,          // minimum level to log
            true,               // bubble = true
            0664
        );

        // Optional processors (adds {channel}, {extra}, etc.)
        $logger->pushProcessor(new PsrLogMessageProcessor());

        // Attach handlers
        $logger->pushHandler($appHandler);
        $logger->pushHandler($errorHandler);

        self::$instance = $logger;
    }


    /**
     * Get the global logger instance.
     * Returns NullLogger if not yet initialized.
     */
    public static function get(): LoggerInterface
    {
        if (!self::$instance) {
            return new NullLogger();
        }
        return self::$instance;
    }
}