<?php
declare(strict_types=1);

namespace RKW\OaiConnector\Security;

/**
 * Knows which requests are public OAI.
 */
final class OaiRequestMatcher
{
    /**
     * @var string[]
     */
    private array $verbs;

    /**
     * @var string[]
     */
    private array $allowedRepos;

    /**
     * @param string[] $verbs
     * @param string[] $allowedRepos
     */
    public function __construct(array $verbs, array $allowedRepos = [])
    {
        $this->verbs = array_map('strtolower', $verbs);
        $this->allowedRepos = $allowedRepos;
    }


    /**
     * Public OAI if:
     * - GET/HEAD
     * - controller=endpoint & action=handle
     * - verb in whitelist
     * - (optional) repo allowed
     */
    public function isPublicOaiRequest(array $get, array $server): bool
    {
        $method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['GET','HEAD'], true)) {
            return false;
        }

        $controller = strtolower((string)($get['controller'] ?? ''));
        $action     = strtolower((string)($get['action'] ?? ''));
        if (!($controller === 'endpoint' && $action === 'handle')) {
            return false;
        }

        $verb = strtolower((string)($get['verb'] ?? ''));
        if ($verb === '' || !in_array($verb, $this->verbs, true)) {
            return false;
        }

        if (!empty($this->allowedRepos)) {
            $repo = (string)($get['repo'] ?? '');
            if (!in_array($repo, $this->allowedRepos, true)) {
                return false;
            }
        }

        return true;
    }
}