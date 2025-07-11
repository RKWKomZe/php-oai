# RKW OAI-PMH Connector

This project connects (amon others) a Shopware system to an OAI-PMH server. It is a deliberately minimal PHP project without a full framework but with a modern structure and Composer support.


## Setup
Create the project folder:
```
mkdir rkw-oaipmh && cd rkw-oaipmh
```
Clone project:
```
git clone git@github.com:RKWKomZe/php-oai.git .

ddev config (with everything default)

ddev get ddev/ddev-phpmyadmin

ddev start
```
Install:
```
ddev exec composer install
```
Add the local .env
```
APP_ENV=development
APP_DEBUG=true
APP_URL=https://rkw-oaipmh.ddev.site

DB_HOST=db
DB_NAME=db
DB_USER=db
DB_PASS=db
DB_PORT=3306
DB_CHARSET='utf8mb4'

SHOPWARE_BASE_URL=https://ddev-rkw-shopware-web
SHOPWARE_CLIENT_ID=SWIABHK5R0FNOWVPQK1FTMFQDW
SHOPWARE_CLIENT_SECRET=STJaY1pCSkVaQ3JqUU43dmJ4SHpmZVVrN1pzNWJvSWlhazNuSFY
```
### Add the database
Add database:
```
Execute from project root:
bash Scripts/setup.sh
```

Enjoy:

https://rkw-oaipmh.ddev.site/

## First steps
* Create your first repo (https://rkw-oaipmh.ddev.site/index.php?controller=repo&action=new)
  * ID "shopware"
  * Base URL: Your domain with following path including your repository name: https://rkw-oaipmh.ddev.site/index.php?controller=endpoint&action=handle&verb=Identify&repo=shopware

* Create your first meta (https://rkw-oaipmh.ddev.site/index.php?controller=meta&action=new)
  * Choose your newly created repo and use the prefill function on the top right ("MARCXML übernehmen")

## 🔧 Project Structure

```
/web               → Web entry point (index.php, import_shopware.php)
/Classes           → PHP classes (e.g. ShopwareOaiFetcher, OaiImporter)
/config/config.php → Configuration (environment, Shopware, DB)
/logs              → Runtime logs (not versioned)
/vendor            → Composer dependencies
/packages/oai-pmh/
└── composer.json  ← includes: "name": "cbisiere/oai-pmh"
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


## 🗂️ Datenbankschema (OAI-Server)

-> Siehe lib

---

### Data array from shopware request

```
 array:1 [▼
  0 => array:97 [▼
    "extensions" => array:2 [▶]
    "_uniqueIdentifier" => "0197350a258d7dfa9629b219fc416fed"
    "versionId" => "0fa91ce3e96a4bc2be4bd9ce752c3425"
    "translated" => array:10 [▶]
    "createdAt" => "2025-06-03T09:06:40.618+00:00"
    "updatedAt" => "2025-06-10T06:28:34.196+00:00"
    "parentId" => null
    "childCount" => 0
    "autoIncrement" => 1
    "taxId" => "0197349d1f8e70e6ba0152c7f8adb80e"
    "manufacturerId" => "0197349d1fa27228a30b6baa1fd614c8"
    "unitId" => null
    "active" => true
    "displayGroup" => "dcbb0710a89ef4f2ab66f042eea20c66"
    "price" => array:1 [▶]
    "manufacturerNumber" => null
    "ean" => null
    "sales" => 0
    "productNumber" => "SW10000"
    "stock" => 10
    "availableStock" => 10
    "available" => true
    "deliveryTimeId" => null
    "deliveryTime" => null
    "restockTime" => null
    "isCloseout" => false
    "purchaseSteps" => 1
    "maxPurchase" => null
    "minPurchase" => 1
    "purchaseUnit" => null
    "referenceUnit" => null
    "shippingFree" => false
    "purchasePrices" => array:1 [▶]
    "markAsTopseller" => false
    "weight" => null
    "width" => null
    "height" => null
    "length" => null
    "releaseDate" => null
    "categoryTree" => array:1 [▶]
    "streamIds" => null
    "optionIds" => null
    "propertyIds" => null
    "name" => "Testprodukt"
    "keywords" => null
    "description" => "Dies ist eine Beschreibung"
    "metaDescription" => null
    "metaTitle" => null
    "packUnit" => null
    "packUnitPlural" => null
    "variantRestrictions" => null
    "variantListingConfig" => array:5 [▶]
    "variation" => []
    "tax" => array:15 [▶]
    "manufacturer" => null
    "unit" => null
    "prices" => []
    "cover" => null
    "parent" => null
    "children" => null
    "media" => null
    "cmsPageId" => "7a6d253a67204037966f42b0119704d5"
    "cmsPage" => null
    "slotConfig" => null
    "searchKeywords" => null
    "translations" => null
    "categories" => null
    "customFieldSets" => null
    "tags" => null
    "properties" => null
    "options" => null
    "configuratorSettings" => null
    "categoriesRo" => null
    "coverId" => null
    "visibilities" => null
    "tagIds" => null
    "categoryIds" => array:1 [▶]
    "productReviews" => null
    "ratingAverage" => null
    "mainCategories" => null
    "seoUrls" => null
    "orderLineItems" => null
    "crossSellings" => null
    "crossSellingAssignedProducts" => null
    "featureSetId" => "0197349d381c7368a1cde50e6dcf8caa"
    "featureSet" => null
    "customFieldSetSelectionActive" => null
    "customSearchKeywords" => null
    "wishlists" => null
    "canonicalProductId" => null
    "canonicalProduct" => null
    "streams" => null
    "downloads" => null
    "states" => array:1 [▶]
    "customFields" => null
    "id" => "0197350a258d7dfa9629b219fc416fed"
    "apiAlias" => "product"
  ]
]


```


## OPTIONAL: Create your local shopware project for test-purposes
### Create a new network to wire both projects:
```
docker network create rkw-sharednet
```
Check it:
```
docker network ls
```
Add this file to both projects inside **.ddev**:
```
docker-compose.override.yaml

version: '3.6'

services:
  web:
    networks:
      - default
      - rkw-sharednet

networks:
  rkw-sharednet:
    external: true

```
If project already exists: Restart DDEV
```
ddev restart
```
### Create your local shopware test environment
Use quickstart from https://ddev.readthedocs.io/en/stable/users/quickstart/#shopware
```
mkdir rkw-shopware && cd rkw-shopware
ddev config --project-type=shopware6 --docroot=public

**ADD docker-compose.override.yaml from above**

ddev start
ddev composer create-project shopware/production
# If it asks `Do you want to include Docker configuration from recipes?`
# answer `x`, as we're using DDEV for this rather than its recipes.
ddev exec console system:install --basic-setup
ddev launch /admin
# Default username and password are `admin` and `shopware`
```
After login use configuration popup for your quickstart:
- "Use demo data"
- "Storefront"
- "Use local email agent"
- Skip PayPal stuff
- Extensions (optional)
- Skip "Shopware Account"
- Skip "Shopware Store"
- Finish

### Create API integration in shopware
* Login to shopware
* Go to **Settings** -> **Integrations** (System) 
* Click on **Add integration** button
* Form values:
  * Name: "RKW OAI Connector"
  * Administration ON (needed for single queries!)
  * SAVE THE SHOWN KEYS (!) and put them into the .env file of the **rkw-oaipmh** project
  * "Save integration" (button)

## 📄 License

This project is currently considered internal or experimental — please update license and usage terms if needed.