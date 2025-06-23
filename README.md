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


## 🗂️ Datenbankschema (OAI-Server)

Das Projekt verwendet drei zentrale Tabellen zur Verwaltung von OAI-PMH-konformen Metadaten. Diese Struktur ermöglicht eine saubere Trennung von Kerninformationen, zusätzlichen About-Daten und Sets.

---

### 📄 `oai_item_meta`

**Beschreibung:**  
Zentrale Tabelle für OAI-Metadaten. Sie enthält sowohl technische Informationen (Versionierung, Status) als auch das Metadatenformat und den serialisierten XML-Inhalt.

| Spalte            | Typ               | Beschreibung                                                                 |
|-------------------|-------------------|------------------------------------------------------------------------------|
| `repo`            | VARCHAR(12)       | Repository-ID, verweist auf `oai_repo`                                       |
| `history`         | TINYINT UNSIGNED  | Historienflag (0 = aktiv, 1 = archiviert)                                   |
| `serial`          | INT UNSIGNED      | Fortlaufende Nummer zur Identifikation pro Repository                       |
| `identifier`      | VARCHAR(200)      | Eindeutiger OAI-Identifier                                                   |
| `metadataPrefix`  | VARCHAR(20)       | Metadatenformat (z. B. `oai_dc`)                                             |
| `datestamp`       | DATETIME          | Änderungszeitpunkt                                                          |
| `deleted`         | TINYINT           | Status (0 = aktiv, 1 = gelöscht)                                            |
| `metadata`        | TEXT              | XML-Daten des Metadatensatzes                                               |
| `created`         | DATETIME          | Erstellzeitpunkt                                                            |
| `updated`         | TIMESTAMP         | Letzte Änderung (automatisch aktualisiert)                                  |

**Indizes und Schlüssel:**
- `PRIMARY KEY (repo, history, serial, identifier, metadataPrefix)`
- `INDEX idx_repo_item_meta (repo, identifier, metadataPrefix)`
- `FOREIGN KEY` zu `oai_repo(id)`
- `FOREIGN KEY` zu `oai_meta(repo, metadataPrefix)`

---

### 📄 `oai_item_meta_about`

**Beschreibung:**  
Tabelle zur Ablage von `<about>`-Informationen für einen Metadatensatz. Jeder Metadatensatz kann mehrere About-Blöcke enthalten, z. B. Rechte, Provenienz, Validierung etc.

| Spalte            | Typ               | Beschreibung                                                                 |
|-------------------|-------------------|------------------------------------------------------------------------------|
| `repo`            | VARCHAR(12)       | Repository-ID                                                                |
| `history`         | TINYINT UNSIGNED  | Historienflag (0 = aktiv, 1 = archiviert)                                   |
| `serial`          | INT UNSIGNED      | Serial des zugehörigen Metadatensatzes                                      |
| `identifier`      | VARCHAR(200)      | Identifier des Metadatensatzes                                              |
| `metadataPrefix`  | VARCHAR(20)       | Metadatenformat                                                              |
| `datestamp`       | DATETIME          | Änderungszeitpunkt                                                          |
| `about`           | TEXT              | XML-Daten im About-Bereich                                                  |
| `rank`            | INT               | Reihenfolgeindex bei mehreren About-Blöcken                                 |
| `created`         | DATETIME          | Erstellzeitpunkt                                                            |
| `updated`         | TIMESTAMP         | Letzte Änderung                                                              |

**Indizes und Schlüssel:**
- `PRIMARY KEY (repo, history, serial, identifier, metadataPrefix, rank)`
- `INDEX idx_repo_item_meta_about (repo, identifier, metadataPrefix, rank)`
- `FOREIGN KEY` zu `oai_repo(id)`
- `FOREIGN KEY` zu `oai_meta(repo, metadataPrefix)`
- `FOREIGN KEY` zu `oai_item_meta(repo, identifier, metadataPrefix)`

**Trigger:**
- `trigger_oai_about_soft_delete`: Wird `oai_item_meta.history` auf `1` gesetzt, werden automatisch alle zugehörigen About-Einträge als archiviert markiert.

---

### 📄 `oai_item_set`

**Beschreibung:**  
Zuweisungstabelle zur Verbindung von OAI-Items mit Sets. Unterstützt mehrere Sets pro Metadatensatz und enthält auch Set-Metadaten.

| Spalte            | Typ               | Beschreibung                                                                 |
|-------------------|-------------------|------------------------------------------------------------------------------|
| `repo`            | VARCHAR(12)       | Repository-ID                                                                |
| `history`         | TINYINT UNSIGNED  | Historienflag                                                                |
| `serial`          | INT UNSIGNED      | Serial des Metadatensatzes                                                  |
| `identifier`      | VARCHAR(200)      | Identifier                                                                   |
| `metadataPrefix`  | VARCHAR(20)       | Metadatenformat                                                              |
| `setSpec`         | VARCHAR(60)       | Set-Kennung (z. B. `libellen`)                                               |
| `confirmed`       | INT UNSIGNED      | Kennzeichen zur Freigabe (optional)                                          |
| `created`         | DATETIME          | Erstellzeitpunkt                                                            |
| `updated`         | TIMESTAMP         | Letzte Änderung                                                              |

**Indizes und Schlüssel:**
- `PRIMARY KEY (repo, history, serial, identifier, metadataPrefix, setSpec)`
- `FOREIGN KEY` zu `oai_repo(id)`
- `FOREIGN KEY` zu `oai_meta(repo, metadataPrefix)`
- `FOREIGN KEY` zu `oai_set(repo, setSpec)`
- `FOREIGN KEY` zu `oai_item_meta(repo, identifier, metadataPrefix)`

---

### 🔗 Beziehungen

- `oai_item_meta` → 1:n → `oai_item_meta_about`
- `oai_item_meta` → 1:n → `oai_item_set`
- Soft-Deletion von `about`-Daten über Trigger bei Archivierung von `meta`


## 📄 License

This project is currently considered internal or experimental — please update license and usage terms if needed.