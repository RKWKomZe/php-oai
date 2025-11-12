<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Utility;

/**
 * Lightweight utility to read or tail Monolog log files.
 *
 * Supports RotatingFileHandler naming (e.g. app-YYYY-MM-DD.log).
 */
final class LogViewer
{
    /**
     * Base log directory — adjust to your project structure.
     * Example: __DIR__ . '/../../logs'
     */
    private static string $logDir = __DIR__ . '/../../logs';

    /**
     * Optionally override base log directory at runtime.
     */
    public static function setLogDir(string $path): void
    {
        self::$logDir = rtrim($path, '/');
    }

    /**
     * Get base log directory.
     */
    public static function getLogDir(): string
    {
        return self::$logDir;
    }

    /**
     * Return most recent log files matching a pattern, newest first.
     *
     * @param string $pattern Like 'app-*.log' or 'error-*.log'
     * @param int $maxFiles   How many to consider (default: 7)
     * @return string[]
     */
    public static function recentFiles(string $pattern, int $maxFiles = 7): array
    {
        $glob = self::$logDir . '/' . ltrim($pattern, '/');
        $files = glob($glob, GLOB_NOSORT) ?: [];
        usort($files, fn(string $a, string $b) => filemtime($b) <=> filemtime($a));
        return array_slice($files, 0, $maxFiles);
    }

    /**
     * Tail last N lines from a single file efficiently.
     */
    public static function tailFile(string $file, int $lines = 200): string
    {
        if (!is_file($file)) {
            return '';
        }

        $fp = fopen($file, 'rb');
        if (!$fp) {
            return '';
        }

        $buffer = '';
        $chunk  = 4096;
        $pos    = -1;
        $lineCount = 0;
        fseek($fp, 0, SEEK_END);
        $fileSize = ftell($fp);

        while ($fileSize > 0 && $lineCount <= $lines) {
            $seek = max($fileSize - $chunk, 0);
            $read = $fileSize - $seek;
            fseek($fp, $seek);
            $data = fread($fp, $read);
            $buffer = $data . $buffer;
            $fileSize = $seek;
            $lineCount = substr_count($buffer, "\n");
            if ($fileSize === 0) break;
        }

        fclose($fp);
        $rows = explode("\n", rtrim($buffer, "\n"));
        $rows = array_slice($rows, -$lines);
        return implode("\n", $rows);
    }

    /**
     * Tail across multiple rotated log files (newest first)
     */
    public static function tailAcross(string $pattern, int $maxLines = 200, int $maxFiles = 7): string
    {
        $files = self::recentFiles($pattern, $maxFiles);
        $collected = [];
        $wanted = $maxLines;

        foreach ($files as $file) {
            $text = self::tailFile($file, $wanted);
            if ($text === '') continue;

            $lines = explode("\n", $text);
            $collected[] = $lines;

            $wanted -= count($lines);
            if ($wanted <= 0) break;
        }

        // Merge oldest → newest
        $merged = [];
        for ($i = count($collected) - 1; $i >= 0; $i--) {
            $merged = array_merge($merged, $collected[$i]);
        }

        return implode("\n", $merged);
    }


    /**
     * Convenience wrappers for app/error logs
     */
    public static function tailApp(int $lines = 200): string
    {
        return self::tailAcross('app-*.log', $lines);
    }


    /**
     * @param int $lines
     * @return string
     */
    public static function tailError(int $lines = 200): string
    {
        // Try rotated files first (error-YYYY-MM-DD.log)
        $rotated = self::recentFiles('error-*.log', 7);
        if (!empty($rotated)) {
            return self::tailAcross('error-*.log', $lines, 7);
        }

        // Fallback: single error.log (non-rotating)
        $single = self::$logDir . '/error.log';
        return self::tailFile($single, $lines);
    }
}