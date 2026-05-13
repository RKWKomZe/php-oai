<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Tests\Security;

use PHPUnit\Framework\TestCase;
use RKW\OaiConnector\Security\OaiRouteBootstrap;

final class OaiRouteBootstrapTest extends TestCase
{

    public function testNormalizeMapsPathBasedOaiRequestToEndpointController(): void
    {
        $get = [
            'verb' => 'Identify',
        ];
        $server = [
            'PATH_INFO' => '/oai/rkw',
            'HTTP_HOST' => 'oai.example.test',
            'HTTPS' => 'on',
            'SCRIPT_NAME' => '/index.php',
        ];

        (new OaiRouteBootstrap())->normalize($get, $server);

        self::assertSame('endpoint', $get['controller']);
        self::assertSame('handle', $get['action']);
        self::assertSame('rkw', $get['repo']);
        self::assertSame('https://oai.example.test/index.php/oai/rkw', $server['OAI_BASE_URL']);
    }


    public function testNormalizeBuildsHarvesterSafeBaseUrlForLegacyQueryRoute(): void
    {
        $get = [
            'controller' => 'endpoint',
            'action' => 'handle',
            'repo' => 'rkw',
            'verb' => 'Identify',
        ];
        $server = [
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'oai.rkw.test',
            'SCRIPT_NAME' => '/index.php',
        ];

        (new OaiRouteBootstrap())->normalize($get, $server);

        self::assertSame('https://oai.rkw.test/index.php/oai/rkw', $server['OAI_BASE_URL']);
    }


    public function testNormalizeUsesRequestUriWhenPathInfoIsMissing(): void
    {
        $get = [
            'verb' => 'ListRecords',
        ];
        $server = [
            'REQUEST_URI' => '/index.php/oai/rkw?verb=ListRecords',
            'SCRIPT_NAME' => '/index.php',
            'HTTP_HOST' => 'oai.example.test',
            'REQUEST_SCHEME' => 'https',
        ];

        (new OaiRouteBootstrap())->normalize($get, $server);

        self::assertSame('endpoint', $get['controller']);
        self::assertSame('handle', $get['action']);
        self::assertSame('rkw', $get['repo']);
    }

}
