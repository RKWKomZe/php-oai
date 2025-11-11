<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Security;

use RKW\OaiConnector\Security\AuthService;
use RKW\OaiConnector\Utility\ConfigLoader;

/**
 * Orchestrator: wires matcher, headers, and auth.
 *
 * Minimal app-level gatekeeper to protect everything except OAI endpoint.
 * - Allows OAI endpoint by query: controller=endpoint & action=handle & verb in whitelist
 * - Provides session login, HTTP Basic verification, and short-lived signed "magic link"
 * - Adds minimal hardening headers
 *
 * USAGE (very first line in public/index.php, before any output):
 *   (new \ABC\OaiConnector\Security\Gatekeeper())->handle();
 */
final class Gatekeeper
{
    private OaiRequestMatcher $matcher;
    private Headers $headers;
    private AuthService $auth;

    public function __construct()
    {
        // Load config from your project
        $cfg = ConfigLoader::load()['security']['gatekeeper'] ?? [];

        // Fail fast on missing secrets
        if (empty($cfg['adminPassHash']) || empty($cfg['tokenSecret'])) {
            throw new \RuntimeException(
                'Gatekeeper: missing adminPassHash or tokenSecret in configuration.'
            );
        }

        // Construct collaborators
        $this->matcher = new OaiRequestMatcher(
        // allowed verbs
            [
                'identify',
                'listmetadataformats',
                'listsets',
                'listidentifiers',
                'listrecords',
                'getrecord'
            ],
            // allowed repos (empty array => allow all)
            $cfg['allowedRepos'] ?? []
        );

        $this->headers = new Headers();

        $tokenService = new TokenService(
            (string)$cfg['tokenSecret'],
            (int)($cfg['tokenTtl'] ?? 900)
        );

        $this->auth = new AuthService(
            (string)($cfg['adminUser'] ?? 'admin'),
            (string)$cfg['adminPassHash'],
            $tokenService
        );
    }

    public function handle(): void
    {
        // Security headers for all requests
        $this->headers->sendBaseHardening();

        // Optional early logout
        if (isset($_GET['logout'])) {
            $this->auth->logout();
            return; // logout() redirects
        }

        // Public OAI? Allow and add OAI-friendly cache headers
        if ($this->matcher->isPublicOaiRequest($_GET, $_SERVER)) {
            $this->headers->sendOaiCache();
            return;
        }

        // Try token exchange (?login_token=...)
        if ($this->auth->tryTokenLogin()) {
            return; // success implied redirect
        }

        // Try HTTP Basic
        if ($this->auth->tryHttpBasic($_SERVER)) {
            return;
        }

        // Try form login
        if ($this->auth->tryFormLogin($_POST, $_SERVER)) {
            return;
        }

        // Session already authenticated?
        if ($this->auth->hasSessionAuth()) {
            return;
        }

        // Otherwise show minimal login form
        $this->auth->renderLogin();
    }
}