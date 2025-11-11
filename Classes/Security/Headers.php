<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Security;

/**
 * Sends conservative security/cache headers.
 */
final class Headers
{
    /** Security headers for all requests. */
    public function sendBaseHardening(): void
    {
        header('Referrer-Policy: no-referrer');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('X-Robots-Tag: noindex, nofollow');
    }

    /** Cache headers for OAI responses. */
    public function sendOaiCache(): void
    {
        header('Cache-Control: public, max-age=300, stale-while-revalidate=60');
    }
}