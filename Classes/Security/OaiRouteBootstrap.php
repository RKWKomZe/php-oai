<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Security;

/**
 * Normalizes path-based OAI endpoint requests before auth/routing kicks in.
 */
final class OaiRouteBootstrap
{

    public function normalize(array &$get, array &$server): void
    {
        $repoFromPath = $this->extractRepoFromPath($server);
        if ($repoFromPath !== null) {
            $get['controller'] ??= 'endpoint';
            $get['action'] ??= 'handle';
            $get['repo'] ??= $repoFromPath;
        }

        if (
            strtolower((string)($get['controller'] ?? '')) === 'endpoint'
            && strtolower((string)($get['action'] ?? '')) === 'handle'
            && ($get['repo'] ?? '') !== ''
        ) {
            $server['OAI_BASE_URL'] = $this->buildBaseUrl($server, (string)$get['repo']);
        }
    }


    private function extractRepoFromPath(array $server): ?string
    {
        $pathInfo = (string)($server['PATH_INFO'] ?? '');
        if ($pathInfo === '') {
            $requestPath = parse_url((string)($server['REQUEST_URI'] ?? ''), PHP_URL_PATH);
            $scriptName = (string)($server['SCRIPT_NAME'] ?? '');

            if (is_string($requestPath) && $requestPath !== '' && $scriptName !== '') {
                if ($requestPath === $scriptName) {
                    $pathInfo = '';
                } elseif (str_starts_with($requestPath, $scriptName . '/')) {
                    $pathInfo = substr($requestPath, strlen($scriptName));
                }
            }
        }

        $normalizedPath = trim($pathInfo, '/');
        if (!preg_match('#^oai/([^/]+)$#', $normalizedPath, $matches)) {
            return null;
        }

        $repo = rawurldecode($matches[1]);
        return $repo !== '' ? $repo : null;
    }


    private function buildBaseUrl(array $server, string $repo): string
    {
        $scheme = 'http';
        $forwardedProto = trim((string)($server['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($forwardedProto !== '') {
            $scheme = strtolower(strtok($forwardedProto, ','));
        } elseif (($server['HTTPS'] ?? '') !== '' && strtolower((string)$server['HTTPS']) !== 'off') {
            $scheme = 'https';
        } elseif (($server['REQUEST_SCHEME'] ?? '') !== '') {
            $scheme = (string)$server['REQUEST_SCHEME'];
        }

        $host = trim((string)($server['HTTP_X_FORWARDED_HOST'] ?? $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost'));
        $host = strtok($host, ',') ?: $host;

        $scriptName = (string)($server['SCRIPT_NAME'] ?? '/index.php');
        if ($scriptName === '') {
            $scriptName = '/index.php';
        }

        return sprintf(
            '%s://%s%s/oai/%s',
            rtrim($scheme, ':/'),
            $host,
            $scriptName,
            rawurlencode($repo)
        );
    }

}
