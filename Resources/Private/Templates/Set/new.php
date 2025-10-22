<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink('Set', 'list', [], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create new Set</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <form method="post" action="?controller=set&action=create" id="setForm" novalidate>
            <div class="mb-3">
                <label for="repo" class="form-label">Repository *</label>
                <select class="form-select" name="repo" id="repo" required>
                    <option value="" disabled selected hidden>Please select...</option>
                    <?php foreach ($repoList as $repoItem): ?>
                        <option value="<?= htmlspecialchars($repoItem->getId()) ?>">
                            <?= htmlspecialchars($repoItem->getRepositoryName() . ' (' . $repoItem->getId() . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="setSpec" class="form-label">Set Spec *</label>
                <input type="text" class="form-control" name="setSpec" id="setSpec" required
                       placeholder="e.g. pubs or data" pattern="^[a-zA-Z0-9._-]+$"
                       title="Only letters, numbers, dot, underscore and hyphen are allowed">
            </div>

            <?php include __DIR__ . '/../../Partials/Set/FormFields.php'; ?>

            <div class="d-flex justify-content-between">
                <a class="btn btn-secondary" href="?controller=set&action=list">Back</a>
                <button type="submit" class="btn btn-primary">Create Set</button>
            </div>
        </form>
    </div>
    <div class="col-md-6">
        <div class="alert alert-warning" role="alert">
            <strong>Note:</strong> The repository and setSpec values define a unique identifier and <strong>cannot be changed after creation.</strong>
            Please create sets intentionally, as they are used as stable references for harvesting in OAI-PMH.
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../Partials/Set/JavaScriptFooter.php'; ?>
