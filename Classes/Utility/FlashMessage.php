<?php

namespace RKW\OaiConnector\Utility;

class FlashMessage
{
    public const TYPE_PRIMARY   = 'primary';
    public const TYPE_SECONDARY = 'secondary';
    public const TYPE_SUCCESS   = 'success';
    public const TYPE_DANGER    = 'danger';
    public const TYPE_WARNING   = 'warning';
    public const TYPE_INFO      = 'info';
    public const TYPE_LIGHT     = 'light';
    public const TYPE_DARK      = 'dark';

    public static function add(string $message, string $type = self::TYPE_INFO): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['flash_messages'][] = [
            'text' => $message,
            'type' => $type
        ];
    }

    public static function getAll(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    public static function hasMessages(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return !empty($_SESSION['flash_messages']);
    }
}
