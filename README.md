# RKW OAI-PMH Connector

This project connects (amon others) a Shopware system to an OAI-PMH server. It is a deliberately minimal PHP project without a full framework but with a modern structure and Composer support.

## Setup
In project root folder: bash Scripts/setup.sh


## 🔧 Project Structure

```
/web               → Web entry point (index.php, import_shopware.php)
/Classes           → PHP classes (e.g. ShopwareOaiFetcher, OaiImporter)
/config/config.php → Configuration (environment, Shopware, DB)
/logs              → Runtime logs (not versioned)
/vendor            → Composer dependencies
```

## ⚙️ Configuration

### `config/config.php`

This file contains the main project configuration, including:

- Environment (`development`, `production`)
- Shopware API access
- Database connection
- Logging setup

```php
return [
    'environment' => 'development',

    'shopware' => [
        'base_url' => 'https://ddev-rkw-shopware-web',
        'client_id' => '...',
        'client_secret' => '...',
    ],

    'database' => [
        'host' => 'localhost',
        'user' => 'oai_user',
        'password' => 'demo',
        'name' => 'oai_repo',
    ],

    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../logs/app.log',
        'level' => 'debug',
    ]
];
```

> 🔐 Sensitive data can optionally be stored in a `.env` file and accessed using `getenv()`.


## 🧩 Configuration Access Helper

A centralized utility class `ConfigLoader` is used to load configuration settings uniformly throughout the project. This avoids messy relative path logic in every script or class.

**Location:** `/Classes/Utility/ConfigLoader.php`

**Usage Example**
```php
use use RKW\OaiConnector\Utility\ConfigLoader;;

$config = ConfigLoader::load();
$dbName = $config['database']['name'];
```
---

## 📦 Used Packages

### [`symfony/var-dumper`](https://packagist.org/packages/symfony/var-dumper)
Provides well-formatted and browser-friendly debug output. Replaces plain `var_dump()`/`print_r()`.

```php
use Symfony\Component\VarDumper\VarDumper;
VarDumper::dump($myData);
```

### [`monolog/monolog`](https://packagist.org/packages/monolog/monolog)
A powerful logging library that supports file logs, email, streams, and more.

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('oai');
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));
$log->debug('Import started');
```

---

## ▶️ Entry Point

You can access `/web/index.php` in the browser to:

- Trigger actions (e.g. import)
- View feedback via Bootstrap alerts
- Inspect the overall structure of the project

---

## Pagination
```
use RKW\OaiConnector\Utility\Pagination;

$page = $_GET['page'] ?? 1;
$itemsPerPage = $_GET['perPage'] ?? 25;

$repo = new RepoRepository();
$totalCount = $repo->countBy(['active' => 1]);

$pagination = new Pagination($page, $itemsPerPage, $totalCount);

$entries = $repo->findBy(['active' => 1], ['created_at' => 'DESC'], $pagination);
```


## 📄 License

This project is currently considered internal or experimental — please update license and usage terms if needed.