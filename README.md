# RKW OAI-PMH Connector

Lightweight PHP application to expose Shopware content via an OAI-PMH endpoint (including MARCXML output for DNB-style harvesting workflows).

## Overview

- No full framework, but clear controller/repository/service structure
- OAI-PMH provider based on `cbisiere/oai-pmh` (path package)
- Shopware import flow with list, single import, and full import
- MARCXML builder with DNB preflight checks
- PHPUnit test setup with local snapshots and validator tests

## Requirements

- PHP `>= 8.3`
- Composer
- MySQL / MariaDB
- Optional but recommended: DDEV for local development

## Project Structure

```text
/web                               Web entry point
/Classes                           Application classes
/Resources                         Templates, assets, layout
/config/config.php                 Runtime config (uses .env)
/packages/cbisiere/oai-pmh         OAI-PMH package (path repository)
/tests                             PHPUnit tests
```

## Local Setup

### 1) Clone

```bash
git clone git@github.com:RKWKomZe/php-oai.git rkw-oaipmh
cd rkw-oaipmh
```

### 2) Install dependencies

Without DDEV:

```bash
composer install
```

With DDEV:

```bash
ddev exec composer install
```

### 3) Configure environment

Create `.env` in project root, for example:

```dotenv
APP_ENV=development
APP_DEBUG=true
APP_URL=https://rkw-oaipmh.ddev.site

DEFAULT_REPO=rkw

DB_HOST=db
DB_NAME=db
DB_USER=db
DB_PASS=db
DB_PORT=3306
DB_CHARSET=utf8mb4

SHOPWARE_BASE_URL=https://ddev-rkw-shopware-web
SHOPWARE_CLIENT_ID=your-client-id
SHOPWARE_CLIENT_SECRET=your-client-secret

GATEKEEPER_ADMIN_USER=admin
GATEKEEPER_ADMIN_PASS_HASH=your-password-hash
GATEKEEPER_TOKEN_SECRET=your-token-secret
GATEKEEPER_TOKEN_TTL=900
```

### 4) Database and assets

The repository contains `Scripts/setup.sh` to initialize DB and public symlink.

```bash
bash Scripts/setup.sh
```

If you run without DDEV, set up the DB manually with the SQL files under:

```text
packages/cbisiere/oai-pmh/install/
```

## Run Application

- Browser entrypoint: `web/index.php`
- Typical local URL (DDEV): `https://rkw-oaipmh.ddev.site/`

## OAI Endpoint Example

```text
/index.php?controller=endpoint&action=handle&verb=Identify&repo=rkw
```

MARCXML list records example:

```text
/index.php?controller=endpoint&action=handle&metadataPrefix=marcxml&repo=rkw&verb=ListRecords
```

## Tests

Run tests locally:

```bash
composer test
```

With DDEV:

```bash
ddev exec composer test
```

Run a single test file:

```bash
./vendor/bin/phpunit --configuration phpunit.xml tests/Utility/MarcXmlBuilderSnapshotTest.php
```

## What Is Covered by Tests

- `MarcXmlBuilder` unit tests
- MARCXML snapshot tests (monograph / issue / article)
- `MarcXmlPreflightValidator` unit tests
- `ImportController` readiness report test path

## Notes

- `.env` is ignored by Git and must be provided per environment.
- Logs are written to `/logs`.
- Current deployment workflow installs production dependencies with `--no-dev`; tests are intended for local/CI test jobs.

