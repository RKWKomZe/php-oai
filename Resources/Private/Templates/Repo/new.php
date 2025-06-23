<?php

use RKW\OaiConnector\Utility\LinkHelper;

$returnTo = $_GET['returnTo'] ?? null;
?>
<div class="col-md-6">
    <h1>Create New Repository</h1>

    <?php
    /*
    echo $returnTo
        ? '<a href="' . htmlspecialchars($returnTo) . '" class="btn btn-sm btn-outline-secondary mb-3">&larr; Zurück zur Liste</a>'
        : LinkHelper::renderLink('Item', 'list', ['repo' => $item->getRepo()], '&larr; Zurück zur Liste', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);
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

        <button type="submit" class="btn btn-primary">Create Repository</button>
        <a href="<?= $this->linkHelper->create('Repo', 'list') ?>" class="btn btn-secondary">Cancel</a>
    </form>
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
    granularitySelect.addEventListener('change', function () {
        if (granularitySelect.value === 'YYYY-MM-DD') {
            datestampInput.placeholder = 'YYYY-MM-DD';
            datestampInput.value = '2000-01-01';
        } else {
            datestampInput.placeholder = 'YYYY-MM-DDThh:mm:ssZ';
            datestampInput.value = '2000-01-01T00:00:00Z';
        }
    });

</script>

