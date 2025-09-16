<?php

use RKW\OaiConnector\Utility\LinkHelper;

$returnTo = $_GET['returnTo'] ?? null;
?>
<div class="row">
    <div class="col-md-6">
        <h1>Create New Repository</h1>

        <?php
        /*
        echo $returnTo
            ? '<a href="' . htmlspecialchars($returnTo) . '" class="btn btn-sm btn-outline-secondary mb-3">&larr; Back to list</a>'
            : LinkHelper::renderLink('Item', 'list', ['repo' => $item->getRepo()], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);
        */
        ?>

        <form action="index.php?controller=Repo&action=create" method="post" class="form">
            <div class="form-group">
                <label for="id">ID *</label>
                <input type="text" name="id" id="id" required class="form-control" placeholder="e.g. repo-1">
            </div>

            <div class="form-group">
                <label for="repositoryName">Repository Name *</label>
                <input type="text" name="repositoryName" id="repositoryName" required class="form-control" placeholder="e.g. My Repository">
            </div>

            <div class="form-group">
                <label for="baseURL">Base URL *</label>
                <input type="url" name="baseURL" id="baseURL" required class="form-control" placeholder="https://example.org/oai">
            </div>

            <div class="form-group">
                <label for="protocolVersion">Protocol Version *</label>
                <select name="protocolVersion" id="protocolVersion" required class="form-control">
                    <option value="2.0" selected>2.0 (recommended)</option>
                    <option value="1.1">1.1 (deprecated)</option>
                    <option value="1.0">1.0 (obsolete)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="adminEmails">Admin Email(s)</label>
                <input type="text" name="adminEmails" id="adminEmails" class="form-control" placeholder="e.g. admin@example.org">
            </div>

            <div class="form-group">
                <label for="earliestDatestamp">Earliest Datestamp</label>
                <input type="text" name="earliestDatestamp" id="earliestDatestamp"
                       class="form-control"
                       placeholder="YYYY-MM-DD or YYYY-MM-DDThh:mm:ssZ"
                       value="2000-01-01">
            </div>

            <div class="form-group">
                <label for="deletedRecord">Deleted Record *</label>
                <select name="deletedRecord" id="deletedRecord" required class="form-control">
                    <option value="no" selected>no (recommended)</option>
                    <option value="transient">transient</option>
                    <option value="persistent">persistent</option>
                </select>
            </div>

            <div class="form-group">
                <label for="granularity">Granularity *</label>
                <select name="granularity" id="granularity" required class="form-control">
                    <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                    <option value="YYYY-MM-DDThh:mm:ssZ">YYYY-MM-DDThh:mm:ssZ</option>
                </select>
            </div>

            <div class="form-group">
                <label for="maxListSize">Max List Size</label>
                <input type="number" name="maxListSize" id="maxListSize" class="form-control" min="0" placeholder="e.g. 100">
            </div>

            <div class="form-group">
                <label for="tokenDuration">Token Duration (seconds)</label>
                <input type="number" name="tokenDuration" id="tokenDuration" class="form-control" min="0" placeholder="e.g. 3600">
            </div>

            <div class="form-group">
                <label for="comment">Comment</label>
                <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="Optional notes or description"></textarea>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Set Description (XML)</label>
                <textarea class="form-control" name="description" id="description" rows="5"
                          placeholder="Paste XML here if needed"><?= htmlspecialchars($setDescription ?? '') ?></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a class="btn btn-secondary" href="?controller=repo&action=list">Back</a>
                <button type="submit" class="btn btn-primary">Create Repository</button>
            </div>
            <br/><br/>
        </form>
    </div>
    <div class="col-md-6">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>
            <div>
                <strong>Important:</strong> When creating a new OAI-PMH repository entry, please consider the following:
                <ul class="mb-0">
                    <li><strong>Repository Identifier:</strong> The combination of <code>repositoryName</code> and <code>baseURL</code> should be globally unique and must not change later.</li>
                    <li><strong>Base URL:</strong> Ensure that the baseURL points to the OAI-PMH endpoint (e.g. <code>https://example.org/oai</code>). It must be publicly accessible for harvesters.</li>
                    <li><strong>Metadata Formats:</strong> Make sure that the repository actually supports the expected metadata formats like <code>oai_dc</code> or <code>marcXml</code>. You can verify this using the <code>?verb=ListMetadataFormats</code> endpoint.</li>
                    <li><strong>Duplicate entries:</strong> Avoid creating repositories for the same endpoint multiple times unless you're managing different configurations intentionally.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const granularitySelect = document.getElementById('granularity');
        const datestampInput = document.getElementById('earliestDatestamp');

        form.addEventListener('submit', function (e) {
            const granularity = granularitySelect.value;
            const datestamp = datestampInput.value.trim();

            const regexDate = /^\d{4}-\d{2}-\d{2}$/;
            const regexDateTime = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/;

            let valid = true;

            if (granularity === 'YYYY-MM-DD' && !regexDate.test(datestamp)) {
                alert("Please enter the datestamp in format YYYY-MM-DD.");
                valid = false;
            }

            if (granularity === 'YYYY-MM-DDThh:mm:ssZ' && !regexDateTime.test(datestamp)) {
                alert("Please enter the datestamp in full ISO format: YYYY-MM-DDThh:mm:ssZ");
                valid = false;
            }

            if (!valid) {
                e.preventDefault(); // Prevent submission
            }
        });
    });


    /*
        Erweiterungsmöglichkeit: dynamisches Placeholder-Anpassen

        Optional kannst Du bei Wechsel der Granularität den Placeholder live anpassen:
     */
    /*
    granularitySelect.addEventListener('change', function () {
        if (granularitySelect.value === 'YYYY-MM-DD') {
            datestampInput.placeholder = 'YYYY-MM-DD';
            datestampInput.value = '2000-01-01';
        } else {
            datestampInput.placeholder = 'YYYY-MM-DDThh:mm:ssZ';
            datestampInput.value = '2000-01-01T00:00:00Z';
        }
    });
*/
</script>

