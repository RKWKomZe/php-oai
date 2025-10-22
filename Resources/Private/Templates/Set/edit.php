<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink('Set', 'list', [], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Set</h1>
</div>

<div class="col-md-6">
    <form method="post" action="?controller=set&action=update" id="setForm" novalidate>
        <input type="hidden" name="repo" value="<?= htmlspecialchars($oaiSet->getRepo()) ?>">
        <input type="hidden" name="setSpec" value="<?= htmlspecialchars($oaiSet->getSetSpec()) ?>">

        <div class="mb-3">
            <label for="repo_display" class="form-label">Repository</label>
            <input type="text" readonly class="form-control-plaintext text-muted" id="repo_display"
                   value="<?= htmlspecialchars($oaiSet->getRepo()) ?>"
                   data-bs-toggle="tooltip"
                   title="The repository cannot be changed after creation. This value is used as part of the primary key.">
        </div>

        <div class="mb-3">
            <label for="setSpec_display" class="form-label">Set Spec</label>
            <input type="text" readonly class="form-control-plaintext text-muted" id="setSpec_display"
                   value="<?= htmlspecialchars($oaiSet->getSetSpec()) ?>"
                   data-bs-toggle="tooltip"
                   title="The Set Spec is a stable identifier and must not be changed. If a new set is needed, please create one.">
        </div>

        <?php include __DIR__ . '/../../Partials/Set/FormFields.php'; ?>

        <div class="d-flex justify-content-between">
            <?php
            echo LinkHelper::renderLink(
                'Set',
                'list',
                [],
                'Cancel',
                ['class' => 'btn btn btn-secondary']);
            ?>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('setForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    const firstInvalid = this.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                }
            });
        }
    });
</script>
