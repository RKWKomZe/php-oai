<?php
$today = (new DateTime())->format('Y-m-d');
$oneMonthAgo = (new DateTime('-1 month'))->format('Y-m-d');
?>

<h1 class="mb-4">Import all records</h1>
<div class="row">
    <div class="col-md-6">
        <div class="alert alert-warning" role="alert">
            <strong>Caution:</strong> This action triggers a complete import of all Shopware records into the system.
            <br>
            Please confirm that you understand the consequences before proceeding.
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="enableImport">
            <label class="form-check-label" for="enableImport">
                I understand and want to enable the import button
            </label>
        </div>

        <a href="/index.php?controller=Import&action=run" id="importButton" class="btn btn-danger disabled" tabindex="-1" aria-disabled="true">
            🚨 Run Shopware Import
        </a>
    </div>
    <div class="col-md-6">
        <div class="alert alert-danger d-flex align-items-start" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 mt-1" aria-hidden="true"></i>
            <div>
                <strong>Warning: Full Import Operation</strong>
                <p class="mb-1">
                    This operation will import or update <strong>all available records</strong> from the connected Shopware instance in a single batch.
                </p>
                <p class="mb-1">
                    It is strongly recommended to use this function only for <strong>initial test imports</strong> or when you are fully aware of the implications.
                </p>
                <ul class="mb-1">
                    <li>All products will be processed – regardless of their status.</li>
                    <li>Existing OAI items will be updated where applicable.</li>
                    <li>This may result in a large number of changes being committed to the database.</li>
                </ul>
                <p class="mb-0">
                    For regular operations, we recommend importing items individually or selectively using the filtered API view instead.
                </p>
            </div>
        </div>
    </div>
</div>


<script>
    document.getElementById('enableImport').addEventListener('change', function () {
        const btn = document.getElementById('importButton');
        if (this.checked) {
            btn.classList.remove('disabled');
            btn.removeAttribute('aria-disabled');
            btn.setAttribute('tabindex', '0');
        } else {
            btn.classList.add('disabled');
            btn.setAttribute('aria-disabled', 'true');
            btn.setAttribute('tabindex', '-1');
        }
    });

    document.getElementById('importButton').addEventListener('click', function (event) {
        const confirmed = confirm('⚠️ This will import ALL records from Shopware.\n\nAre you absolutely sure you want to continue?');
        if (!confirmed) {
            event.preventDefault(); // Cancel the navigation
        }
    });
</script>