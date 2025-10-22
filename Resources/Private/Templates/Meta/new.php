<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink('Meta', 'list', [], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Create new metadata format</h1>
    <div class="btn-group" role="group" aria-label="Vorlagen">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fillDublinCore()" title="Fill form with Dublin Core">
            Adopt Dublin Core
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fillMarcxml()" title="Fill form with MARCXML">
            Adopt MARCXML
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <form method="post" action="?controller=meta&action=create" id="metaForm" novalidate>

            <div class="mb-3">
                <label for="repo" class="form-label">Repository</label>
                <select class="form-select" name="repo" id="repo" required>
                    <option value="" disabled selected hidden>-- Please choose --</option>
                    <?php foreach ($repoList as $repoItem): ?>
                        <option value="<?= htmlspecialchars($repoItem->getId()) ?>">
                            <?= htmlspecialchars($repoItem->getRepositoryName() . ' (' . $repoItem->getId() . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="metadataPrefix" class="form-label">Metadaten prefix</label>
                <input type="text" class="form-control" name="metadataPrefix" id="metadataPrefix" required
                       placeholder="z. B. oai_dc oder shop_xml" pattern="^[a-zA-Z0-9._\-]+$"
                       title="Only letters, numbers, dot, underscore and hyphen allowed">
            </div>


            <?php include __DIR__ . '/../../Partials/Meta/FormFields.php'; ?>

            <div class="d-flex justify-content-between">
                <a class="btn btn-secondary" href="?controller=meta&action=list">Back</a>
                <button type="submit" class="btn btn-primary">Create Metadata</button>
            </div>

        </form>
    </div>
    <div class="col-md-6">
        <div class="alert alert-info" role="alert">
            <strong>Information:</strong> You can prefill the metadata field using either <em>Dublin Core</em> or <em>MARCXML</em> templates.
            Dublin Core is a simple, generic metadata format suitable for most use cases. MARCXML is a richer format preferred by national libraries such as the German National Library (DNB).
            Please choose the format that best matches your data and intended harvesting partner.
        </div>
    </div>
</div>

<script>
    function fillDublinCore() {
        const form = document.getElementById('metaForm');

        const prefixField = form.querySelector('[name="metadataPrefix"]');
        const schemaField = form.querySelector('[name="schema"]');
        const namespaceField = form.querySelector('[name="metadataNamespace"]');
        const commentField = form.querySelector('[name="comment"]');

        prefixField.value = 'oai_dc';
        schemaField.value = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
        namespaceField.value = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
        commentField.value = 'Pflichteintrag für Dublin Core laut OAI-PMH-Spezifikation';

        prefixField.focus(); // visuelles Feedback
    }

    function fillMarcxml() {
        const form = document.getElementById('metaForm');

        const prefixField = form.querySelector('[name="metadataPrefix"]');
        const schemaField = form.querySelector('[name="schema"]');
        const namespaceField = form.querySelector('[name="metadataNamespace"]');
        const commentField = form.querySelector('[name="comment"]');

        prefixField.value = 'marcxml';
        schemaField.value = 'http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd';
        namespaceField.value = 'http://www.loc.gov/MARC21/slim';
        commentField.value = 'Pflichteintrag für MARCXML laut OAI-PMH-Spezifikation (Library of Congress)';

        prefixField.focus(); // visual feedback
    }
</script>

<?php include __DIR__ . '/../../Partials/Meta/JavaScriptFooter.php'; ?>