<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Security;

/**
 * Creates and verifies short-lived HMAC tokens.
 */
final class TokenService
{

    /**
     * @var string
     */
    private string $secret;


    /**
     * @var int|mixed
     */
    private int $ttl;


    /**
     * @param string $secret
     * @param int $ttl
     */
    public function __construct(string $secret, int $ttl)
    {
        $this->secret = $secret;
        $this->ttl = max(1, $ttl);
    }


    /**
     * Returns full login link for convenience (optional helper).
     */
    public function createLoginLink(string $baseUrl, string $userId = 'admin'): string
    {
        $payload = base64_encode(json_encode(['uid' => $userId, 'exp' => time() + $this->ttl]));
        $sig     = base64_encode(hash_hmac('sha256', $payload, $this->secret, true));
        return rtrim($baseUrl, '/') . '/index.php?login_token=' . urlencode($payload . '.' . $sig);
    }


    /**
     * Verifies token and returns payload array on success, or null on failure.
     * Payload must contain 'uid' and 'exp'.
     */
    public function verifyToken(string $token): ?array
    {
        if ($token === '') return null;

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) return null;
        [$payloadB64, $sigB64] = $parts;

        $expected = base64_encode(hash_hmac('sha256', $payloadB64, $this->secret, true));
        if (!hash_equals($expected, $sigB64)) return null;

        $raw = base64_decode($payloadB64, true);
        $data = $raw ? json_decode($raw, true) : null;
        if (!is_array($data)) return null;

        $exp = (int)($data['exp'] ?? 0);
        if ($exp < time()) return null;

        return $data;
    }

}