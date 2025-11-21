<?php

namespace RKW\OaiConnector\Utility;

/**
 * FlashMessage
 *
 * Manages flash messages by providing functionality to add, retrieve, and check for messages.
 */
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


    /**
     * Adds a flash message to the session storage.
     *
     * @param string $message The message text to be displayed.
     * @param string $type The type of the message, defaulting to an information type.
     *
     * @return void
     */
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


    /**
     * Retrieves all flash messages from the session and clears them afterwards.
     *
     * @return array<int, string> List of flash messages
     */
    public static function getAll(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }


    /**
     * Checks whether there are any flash messages stored in the session.
     *
     * @return bool True if flash messages exist, false otherwise
     */
    public static function hasMessages(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return !empty($_SESSION['flash_messages']);
    }

}
