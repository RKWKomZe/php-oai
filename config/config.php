<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
    'environment' => $_ENV['APP_ENV'],
    'app' => [
        'name' => 'RKW OAI',
        'timezone' => 'Europe/Berlin',
        'locale' => 'de_DE',
        'debug' => $_ENV['APP_DEBUG'],
        'baseUrl' => $_ENV['APP_URL'],
        'basePath' => realpath(__DIR__ . '/../'),
    ],
    'oai' => [
        'defaultRepoId' => 'maxtest',
    ],
    'api' => [
        'shopware' => [
            'baseUrl' => $_ENV['SHOPWARE_BASE_URL'],
            'clientId' => $_ENV['SHOPWARE_CLIENT_ID'],
            'clientSecret' => $_ENV['SHOPWARE_CLIENT_SECRET'],
        ]
    ],
    'typo3' => [
        'downloadBaseUrl' => 'https://new.rkw-kompetenzzentrum.de',
    ],
    'database' => [
        'host' => $_ENV['DB_HOST'],
        'name' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'port' => 3306,
        'charset' => 'utf8mb4',
    ],
    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../logs/app.log',
        'level' => 'debug', // oder 'error', 'warning', ...
    ],
    'security' => [
        'enable_csrf' => true,
        'admin_ips' => ['127.0.0.1'],
        'gatekeeper' => [
            'adminUser' => $_ENV['GATEKEEPER_ADMIN_USER'],
            'adminPassHash' => $_ENV['GATEKEEPER_ADMIN_PASS_HASH'],
            'tokenSecret' => $_ENV['GATEKEEPER_TOKEN_SECRET'],
            'tokenTtl' => $_ENV['GATEKEEPER_TOKEN_TTL'] ?: '900',
        ]
    ],
    'testing' => [
        'localShopwareUrl' => 'https://rkw-shopware.ddev.site'
    ]

];
