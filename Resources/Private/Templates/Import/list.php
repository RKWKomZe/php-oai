<?php use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\FlashMessageService;
use RKW\OaiConnector\Utility\LinkHelper;
use RKW\OaiConnector\Utility\LocalTestStuff;
use Symfony\Component\VarDumper\VarDumper;

$config = ConfigLoader::load();

$total = $data['totalCount'] ?? null;
$maxPages = $total ? (int) ceil($total / $limit) : null;

$currentPage = (int) ($_GET['page'] ?? 1);
$prevPage = $currentPage > 1 ? $currentPage - 1 : null;
$nextPage = count($productList) === $limit ? $currentPage + 1 : null; // nur wenn Seite voll ist
$queryBase = http_build_query(array_merge($_GET, ['page' => null]));

?>

<h1 class="mb-4">Shopware Produktliste</h1>

<div class="alert alert-info" role="alert">
    <strong>Note:</strong> The product list below is retrieved directly from the Shopware API.
    You can filter live product data by date and number of records per page.

    <a class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="collapse" href="#importHelpDetails" role="button" aria-expanded="false" aria-controls="importHelpDetails">
        Show more...
    </a>

    <div class="collapse mt-2" id="importHelpDetails">
        <p class="mb-1">
            The selected repository is required and allows the system to indicate whether a given product has already been imported into it.
        </p>
        <p class="mb-0">
            To import products into the OAI system, use the action buttons shown next to each record.
        </p>
    </div>
</div>

<form method="get" class="row g-3 mb-4">
    <input type="hidden" name="controller" value="Import">
    <input type="hidden" name="action" value="list">
    <input type="hidden" name="page" value="<?= (int)($_GET['page'] ?? 1) ?>">

    <div class="col-auto">
        <label for="repo" class="form-label">Repository:</label>
        <select name="repo" id="repo" class="form-select">
            <?php foreach ($repoList as $repo): ?>
                <option value="<?= $repo->getId() ?>" <?= $repo->getId() === $activeRepoId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($repo->getRepositoryName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-auto">
        <label for="from" class="form-label">Von:</label>
        <input type="date" id="from" name="from"
               value="<?= htmlspecialchars($fromDate ?? '') ?>"
               class="form-control">
    </div>

    <div class="col-auto">
        <label for="until" class="form-label">Bis:</label>
        <input type="date" id="until" name="until"
               value="<?= htmlspecialchars($untilDate ?? '') ?>"
               class="form-control">
    </div>

    <div class="col-auto">
        <label for="limit" class="form-label">Anzahl:</label>
        <select name="limit" id="limit" class="form-select">
            <?php foreach ([10, 25, 50, 100] as $option): ?>
                <option value="<?= $option ?>" <?= ((int)($_GET['limit'] ?? 25) === $option) ? 'selected' : '' ?>>
                    <?= $option ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-auto align-self-end">
        <button type="submit" class="btn btn-secondary">Apply</button>
    </div>
</form>
<div class="mb-3 small d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill" style="color: #198754; opacity: 0.5; font-size: 1.4rem;"></i>
    <span class="text-muted">Dieses Symbol kennzeichnet bereits importierte Produkte</span>
</div>
<!-- Produktliste -->
<?php foreach ($productList as $product): ?>

    <?php
    $existingRecordIdentifier = "oai:$activeRepoId:" . $product['id'];
    $alreadyImported = in_array($existingRecordIdentifier, $existingIdentifiers, true);
    ?>

    <div class="card mb-4 position-relative" data-product-id-container="<?= $product['id'] ?>">
        <?php if ($alreadyImported): ?>
            <!-- Overlay for already imported products -->
            <div class="imported-overlay">
                <i class="bi bi-check-circle-fill check-icon" title="Bereits importiert"></i>
            </div>
        <?php endif; ?>

        <div class="row g-0">
            <div class="col-md-2 pt-4 text-center">
                <?php if (!empty($product['cover']['media']['url'])): ?>
                    <?php if (!empty($config['environment'] === 'development')): ?>
                        <!-- for testing purpose: The api-URL is a container URL. We need the real ddev URL -->
                        <img src="<?= htmlspecialchars(LocalTestStuff::fixShopwareMediaUrl($product['cover']['media']['url'])) ?>" class="img-fluid rounded-start" alt="Produktbild">
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($product['cover']['media']['url']) ?>" class="img-fluid rounded-start" alt="Produktbild">
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-muted mt-4">No image</div>
                <?php endif; ?>
            </div>
            <div class="col-md-10">
                <div class="card-body">

                    <?php if (!$alreadyImported): ?>
                        <?php
                        echo LinkHelper::renderLink(
                            'import',
                            'importOne',
                            [
                                'id' => $product['id'],
                                'repo' => $activeRepoId
                            ],
                            'Freigabe',
                            [
                                'class' => 'btn btn-sm btn-primary position-absolute top-0 end-0 m-3 import-button',
                                //'onclick' => 'return confirm("Are you sure you want to import this record?")',
                                'data-product-id' => $product['id'],
                                'data-import-url' => '/index.php?controller=import&action=importOne&id=' . $product['id'] . '&repo=shopware',
                            ]);
                        ?>
                    <?php else:
                        ?>
                        <div class="position-absolute top-0 end-0 m-3 d-flex gap-2">
                            <?php
                            echo LinkHelper::renderLink(
                                'import',
                                'importOne',
                                [
                                    'id' => $product['id'],
                                    'repo' => $activeRepoId
                                ],
                                'Re-Import',
                                [
                                    'class' => 'btn btn-sm btn-secondary import-button',
                                    //'onclick' => 'return confirm("Are you sure you want to re-import this record?")',
                                    'data-product-id' => $product['id'],
                                    'data-import-url' => '/index.php?controller=import&action=importOne&id=' . $product['id'] . '&repo=shopware',

                                ]);
                            ?>
                            <?php
                            echo LinkHelper::renderLink(
                                'Item',
                                'show',
                                [
                                    'id' => $existingRecordIdentifier,
                                    'repo' => $activeRepoId,
                                    'returnTo' => $_SERVER['REQUEST_URI']
                                ],
                                'Ansehen',
                                [
                                    'class' => 'btn btn-outline-secondary btn-sm',
                                    //'target' => '_blank'
                                ]);
                            ?>
                        </div>
                    <?php endif; ?>


                    <h5 class="card-title"><?= htmlspecialchars((string)$product['name']) ?></h5>
                    <p class="card-text mb-1"><strong>Artikelnummer:</strong> <?= htmlspecialchars($product['productNumber']) ?></p>
                    <p class="card-text mb-1"><strong>Hersteller:</strong> <?= htmlspecialchars($product['manufacturer']['name'] ?? '-') ?></p>
                    <p class="card-text mb-1"><strong>Beschreibung:</strong> <?= htmlspecialchars($product['description'] ?? '-') ?></p>
                    <p class="card-text mb-1"><strong>Erstellt am:</strong> <?= htmlspecialchars($product['createdAt']) ?></p>
                    <p class="card-text mb-1"><strong>Aktiv:</strong> <?= $product['active'] ? 'Ja' : 'Nein' ?></p>
                    <p class="card-text mb-1"><strong>Lagerbestand:</strong> <?= htmlspecialchars($product['stock']) ?></p>
                    <p class="card-text mb-1"><strong>ID:</strong> <?= htmlspecialchars($product['id']) ?></p>

                    <details class="mt-3">
                        <summary>Weitere Felder anzeigen</summary>
                        <pre class="mt-2 bg-light p-2 border rounded small">
                            <?= htmlspecialchars(json_encode($product, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?>
                        </pre>
                    </details>

                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>


<?php if ($maxPages > 1): ?>
    <nav class="mt-4" aria-label="Shopware-Produktseiten">
        <ul class="pagination justify-content-center rkw-pagination">

            <!-- Zurück -->
            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= $queryBase ?>&page=<?= max(1, $currentPage - 1) ?>" aria-label="Zurück">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <!-- Seitenzahlen (max. 5 anzeigen: zentriert um currentPage) -->
            <?php
            $start = max(1, $currentPage - 2);
            $end = min($maxPages, $currentPage + 2);

            if ($start > 1) {
                echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }

            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= $queryBase ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor;

            if ($end < $maxPages) {
                echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            ?>

            <!-- Weiter -->
            <li class="page-item <?= $currentPage >= $maxPages ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= $queryBase ?>&page=<?= min($maxPages, $currentPage + 1) ?>" aria-label="Weiter">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>

        </ul>

        <!-- Info zur Trefferanzahl -->
        <p class="text-center small text-muted">
            Seite <?= $currentPage ?> von <?= $maxPages ?> &middot;
            <?= $total ?> Produkt<?= $total === 1 ? '' : 'e' ?> gefunden
        </p>
    </nav>
<?php endif; ?>


