<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Security;

/**
 * Handles session, HTTP Basic, form login, token exchange, and logout.
 */
final class AuthService
{
    /**
     * @var string
     */
    private string $adminUser;

    /**
     * @var string
     */
    private string $adminPassHash;

    /**
     * @var TokenService
     */
    private TokenService $tokens;

    /**
     * @param string $adminUser
     * @param string $adminPassHash
     * @param TokenService $tokens
     */
    public function __construct(string $adminUser, string $adminPassHash, TokenService $tokens)
    {
        $this->adminUser = $adminUser;
        $this->adminPassHash = $adminPassHash;
        $this->tokens = $tokens;
    }


    /**
     * Returns true if the current session is authenticated.
     */
    public function hasSessionAuth(): bool
    {
        $this->ensureSession();
        return isset($_SESSION['auth']['uid']);
    }


    /**
     * Try exchange of short-lived token (?login_token=...). Redirects on success.
     */
    public function tryTokenLogin(): bool
    {
        $token = (string)($_GET['login_token'] ?? '');
        if ($token === '') return false;

        $payload = $this->tokens->verifyToken($token);
        if ($payload === null) {
            return false;
        }

        // TODO: implement one-time blacklist by JTI if needed

        $this->ensureSession();
        $_SESSION['auth'] = ['uid' => (string)($payload['uid'] ?? 'token'), 'ts' => time()];

        $this->redirectToPathOnly();
        return true; // execution already redirected
    }


    /**
     * Try HTTP Basic auth using server vars.
     */
    public function tryHttpBasic(array $server): bool
    {
        if (isset($server['PHP_AUTH_USER'], $server['PHP_AUTH_PW'])) {
            return $this->verifyPassword((string)$server['PHP_AUTH_USER'], (string)$server['PHP_AUTH_PW']);
        }

        $hdr = $server['HTTP_AUTHORIZATION'] ?? $server['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (stripos($hdr, 'Basic ') === 0) {
            $decoded = base64_decode(substr($hdr, 6), true);
            if ($decoded !== false && str_contains($decoded, ':')) {
                [$u, $p] = explode(':', $decoded, 2);
                return $this->verifyPassword($u, $p);
            }
        }

        return false;
    }


    /**
     * Try form login (POST user/pass).
     */
    public function tryFormLogin(array $post, array $server): bool
    {
        if (($server['REQUEST_METHOD'] ?? 'GET') !== 'POST') return false;
        if (($post['_act'] ?? '') !== 'login') return false;

        $this->throttle();
        $u = (string)($post['user'] ?? '');
        $p = (string)($post['pass'] ?? '');
        return $this->verifyPassword($u, $p);
    }


    /**
     * Render minimal HTML login form and exit.
     */
    public function renderLogin(): void
    {
        http_response_code(401);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><meta charset="utf-8"><title>Login</title>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:2rem;}
form{max-width:360px}label{display:block;margin:.5rem 0 .25rem}
input{width:100%;padding:.5rem;border:1px solid #ccc;border-radius:.25rem}
button{margin-top:1rem;padding:.5rem .75rem}
</style>
<h1>Restricted</h1>
<form method="post">
  <input type="hidden" name="_act" value="login">
  <label>Username</label>
  <input name="user" autocomplete="username">
  <label>Password</label>
  <input name="pass" type="password" autocomplete="current-password">
  <button type="submit">Login</button>
</form>';
        exit;
    }


    /**
     * Destroy session and redirect to the same path (login screen).
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        @session_destroy();
        $this->redirectToPathOnly();
    }


    /**
     * @param string $user
     * @param string $pass
     * @return bool
     */
    private function verifyPassword(string $user, string $pass): bool
    {
        if ($user !== $this->adminUser) {
            return false;
        }
        if (!password_verify($pass, $this->adminPassHash)) {
            return false;
        }

        $this->ensureSession();
        $_SESSION['auth'] = ['uid' => $user, 'ts' => time()];
        return true;
    }


    /**
     * @return void
     */
    private function throttle(): void
    {
        $this->ensureSession();
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $_SESSION['auth_attempts'][$ip] = (int)($_SESSION['auth_attempts'][$ip] ?? 0) + 1;
        if ($_SESSION['auth_attempts'][$ip] > 5) {
            usleep(500_000); // 0.5s backoff after five attempts
        }
    }


    /**
     * @return void
     */
    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_httponly', '1');
            if ($this->isHttps()) {
                ini_set('session.cookie_secure', '1');
            }
            @session_start();
        }
    }


    /**
     * @return void
     */
    private function redirectToPathOnly(): void
    {
        $scheme = $this->scheme();
        $host   = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
        $path   = strtok((string)($_SERVER['REQUEST_URI'] ?? '/'), '?');
        header('Location: ' . $scheme . '://' . $host . $path, true, 302);
        exit;
    }


    /**
     * @return bool
     */
    private function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
        return false;
    }


    /**
     * @return string
     */
    private function scheme(): string
    {
        return $this->isHttps() ? 'https' : 'http';
    }
}