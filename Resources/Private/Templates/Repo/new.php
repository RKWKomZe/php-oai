<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink('Repo', 'list', [], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>
<div class="row">
    <div class="col-md-6">
        <h1>Create New Repository</h1>

        <form action="index.php?controller=Repo&action=create" method="post" class="form">

            <div class="form-group">
                <label for="id">ID *</label>
                <input
                        id="id"
                        class="form-control"
                        type="text"
                        name="id"
                        placeholder="e.g. repo-1"
                        value="<?= htmlspecialchars((string)$oaiRepo->getId()) ?>"
                        required
                >
            </div>

            <?php include __DIR__ . '/../../Partials/Repo/FormFields.php'; ?>

            <div class="d-flex justify-content-between button-footer">
                <a class="btn btn-secondary" href="?controller=repo&action=list">Back</a>
                <button type="submit" class="btn btn-primary">Create Repository</button>
            </div>

        </form>
    </div>
    <div class="col-md-6">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>
            <div>
                <strong>Important:</strong> When creating a new OAI-PMH repository entry, please consider the following:
                <ul class="mb-0">
                    <li><strong>Repository Identifier:</strong> The combination of <code>repositoryName</code> and <code>baseURL</code> should be globally unique and must not change later.</li>
                    <li><strong>Base URL:</strong> Ensure that the baseURL points to the OAI-PMH endpoint (e.g. <code>https://rkw-oaipmh.ddev.site/index.php?controller=endpoint&action=handle&verb=Identify&repo=myrepoid</code>). It must be publicly accessible for harvesters.</li>
                    <li><strong>Metadata Formats:</strong> Make sure that the repository actually supports the expected metadata formats like <code>oai_dc</code> or <code>marcXml</code>. You can verify this using the <code>?verb=ListMetadataFormats</code> endpoint.</li>
                    <li><strong>Duplicate entries:</strong> Avoid creating repositories for the same endpoint multiple times unless you're managing different configurations intentionally.</li>
                </ul>
            </div>
        </div>
    </div>
</div>



