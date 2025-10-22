<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink('Repo', 'list', [], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>

<div class="col-md-6">
    <h1>Edit Repository</h1>

    <form action="index.php?controller=Repo&action=update" method="post" class="form">
        <!-- Hidden field to preserve ID -->
        <input type="hidden" name="id" value="<?= htmlspecialchars($oaiRepo->getId()) ?>">

        <?php include __DIR__ . '/../../Partials/Repo/FormFields.php'; ?>

        <div class="d-flex justify-content-between button-footer">
            <?php
            echo LinkHelper::renderLink(
                'Repo',
                'list',
                [],
                'Cancel',
                ['class' => 'btn btn btn-secondary']);
            ?>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../Partials/Repo/JavaScriptFooter.php'; ?>