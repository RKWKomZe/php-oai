<?php

$comparison = is_array($data['comparison'] ?? null) ? $data['comparison'] : [];
$mode = (string)($data['mode'] ?? 'force');
$product = $data['product'] ?? [];
$repoId = (string)($data['repoId'] ?? '');
$metadataPrefix = (string)($data['metadataPrefix'] ?? '');
$returnTo = (string)($data['returnTo'] ?? '');

$backUrl = $returnTo !== ''
    ? $returnTo
    : '/index.php?controller=Import&action=list';

$isForceMode = $mode === 'force';
$heading = $isForceMode ? 'Confirm Force Reindex' : 'Confirm Re-Import';
$introTitle = $isForceMode ? 'Technical action:' : 'Source-driven action:';
$introText = $isForceMode
    ? 'This action ignores the normal comparison against Shopware.updatedAt and rebuilds the OAI record using the current importer code.'
    : 'This action uses changed Shopware source data and rebuilds the OAI record using the current importer code.';
$introClass = $isForceMode ? 'alert-warning' : 'alert-info';
$secondaryText = $isForceMode
    ? 'For normal content updates, Re-Import remains the correct action. Force Reindex is intended only for technical changes to mapping, builder, or import logic.'
    : 'This screen shows the current active OAI record next to the newly generated import output before the re-import is executed.';
$datestampNewText = $isForceMode
    ? 'Will be refreshed on force re-import'
    : 'Will be refreshed on re-import';
$storedAtNewText = 'Will be set during the new import run';
$submitAction = $isForceMode ? 'forceReindexOne' : 'importOne';
$submitButtonClass = $isForceMode ? 'btn btn-warning' : 'btn btn-primary';
$submitButtonLabel = $isForceMode ? 'Run Force Reindex' : 'Run Re-Import';
?>

<?= '<a href="' . htmlspecialchars($backUrl) . '" class="btn btn-sm btn-outline-secondary mb-3">&larr; Back to list</a>' ?>

<h1><?= htmlspecialchars($heading) ?></h1>

<div class="alert <?= htmlspecialchars($introClass) ?>" role="alert">
    <strong><?= htmlspecialchars($introTitle) ?></strong> <?= htmlspecialchars($introText) ?>
</div>

<div class="alert alert-secondary" role="alert">
    <?= htmlspecialchars($secondaryText) ?>
</div>

<table class="table table-bordered">
    <tr>
        <th>Product</th>
        <td><?= htmlspecialchars((string)($product['title'] ?? '-')) ?></td>
    </tr>
    <tr>
        <th>Shopware-ID</th>
        <td><code><?= htmlspecialchars((string)($product['identifier'] ?? '-')) ?></code></td>
    </tr>
    <tr>
        <th>Repository</th>
        <td><code><?= htmlspecialchars($repoId) ?></code></td>
    </tr>
    <tr>
        <th>Metadata Prefix</th>
        <td><code><?= htmlspecialchars($metadataPrefix) ?></code></td>
    </tr>
    <tr>
        <th>Shopware source datestamp</th>
        <td><?= htmlspecialchars((string)($product['datestamp'] ?? '-')) ?></td>
    </tr>
</table>

<div class="alert alert-light border" role="alert">
    <strong>Current vs new import output</strong><br>
    <?php foreach (($comparison['changes'] ?? []) as $message): ?>
        <div><?= htmlspecialchars((string)$message) ?></div>
    <?php endforeach; ?>
</div>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Field</th>
        <th>Current active OAI record</th>
        <th>New generated import output</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th>Identifier</th>
        <td><code><?= htmlspecialchars((string)($comparison['currentIdentifier'] ?? '-')) ?></code></td>
        <td><code><?= htmlspecialchars((string)($comparison['newIdentifier'] ?? '-')) ?></code></td>
    </tr>
    <tr>
        <th>Deleted flag</th>
        <td><?= array_key_exists('currentDeleted', $comparison) && $comparison['currentDeleted'] !== null ? ($comparison['currentDeleted'] ? 'true' : 'false') : '-' ?></td>
        <td><?= !empty($comparison) ? (($comparison['newDeleted'] ?? false) ? 'true' : 'false') : '-' ?></td>
    </tr>
    <tr>
        <th>Datestamp</th>
        <td><?= htmlspecialchars((string)($comparison['currentDatestamp'] ?? '-')) ?></td>
        <td><span class="text-muted"><?= htmlspecialchars($datestampNewText) ?></span></td>
    </tr>
    <tr>
        <th>Metadata hash</th>
        <td><code><?= htmlspecialchars((string)($comparison['currentMetadataHash'] !== '' ? $comparison['currentMetadataHash'] : '-')) ?></code></td>
        <td><code><?= htmlspecialchars((string)($comparison['newMetadataHash'] ?? '-')) ?></code></td>
    </tr>
    <tr>
        <th>Metadata changed</th>
        <td colspan="2">
            <?php if (($comparison['metadataChanged'] ?? false) === true): ?>
                <span class="badge text-bg-warning">Yes</span>
            <?php else: ?>
                <span class="badge text-bg-success">No</span>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Stored at</th>
        <td><?= htmlspecialchars((string)($comparison['currentUpdated'] ?? '-')) ?></td>
        <td><span class="text-muted"><?= htmlspecialchars($storedAtNewText) ?></span></td>
    </tr>
    </tbody>
</table>

<details class="mb-4" open>
    <summary>Show metadata XML comparison</summary>

    <div class="row g-3 mt-2">
        <div class="col-12 col-xl-6">
            <h3 class="h5">Current active metadata XML</h3>
            <?php if (trim((string)($comparison['currentMetadata'] ?? '')) === ''): ?>
                <div class="alert alert-light border mb-0">
                    No active metadata XML is currently stored for this repo and metadata prefix.
                </div>
            <?php else: ?>
                <pre class="bg-light p-2 h-100" style="white-space:pre; overflow:auto; max-height: 60vh;"><code class="language-xml"><?=
                        \RKW\OaiConnector\Utility\FormatXml::formatXmlForDisplay((string)($comparison['currentMetadata'] ?? ''));
                    ?></code></pre>
            <?php endif; ?>
        </div>

        <div class="col-12 col-xl-6">
            <h3 class="h5">New generated metadata XML</h3>
            <pre class="bg-light p-2 h-100" style="white-space:pre; overflow:auto; max-height: 60vh;"><code class="language-xml"><?=
                    \RKW\OaiConnector\Utility\FormatXml::formatXmlForDisplay((string)($comparison['newMetadata'] ?? ''));
                ?></code></pre>
        </div>
    </div>
</details>

<div class="mb-5">
    <form method="post" action="/index.php?controller=Import&action=<?= htmlspecialchars($submitAction) ?>" class="d-inline">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)($product['identifier'] ?? '')) ?>">
        <input type="hidden" name="repo" value="<?= htmlspecialchars($repoId) ?>">
        <input type="hidden" name="metadataPrefix" value="<?= htmlspecialchars($metadataPrefix) ?>">
        <input type="hidden" name="returnTo" value="<?= htmlspecialchars($returnTo) ?>">
        <button type="submit" class="<?= htmlspecialchars($submitButtonClass) ?>">
            <?= htmlspecialchars($submitButtonLabel) ?>
        </button>
    </form>
</div>
