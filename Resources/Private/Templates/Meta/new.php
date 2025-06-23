
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Neues Metadatenformat anlegen</h1>
    <div class="btn-group" role="group" aria-label="Vorlagen">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fillDublinCore()" title="Formular mit Dublin Core befüllen">
            Dublin Core übernehmen
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fillMarcxml()" title="Formular mit MARCXML befüllen">
            MARCXML übernehmen
        </button>
    </div>
</div>

<div class="col-md-6">
    <form method="post" action="?controller=meta&action=create" id="metaForm" novalidate>
        <div class="mb-3">
            <label for="repo" class="form-label">Repository</label>
            <select class="form-select" name="repo" id="repo" required>
                <option value="" disabled selected hidden>Bitte wählen...</option>
                <?php foreach ($repoList as $repoItem): ?>
                    <option value="<?= htmlspecialchars($repoItem->getId()) ?>">
                        <?= htmlspecialchars($repoItem->getRepositoryName() . ' (' . $repoItem->getId() . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="metadataPrefix" class="form-label">Metadaten-Prefix</label>
            <input type="text" class="form-control" name="metadataPrefix" id="metadataPrefix" required
                   placeholder="z. B. oai_dc oder shop_xml" pattern="^[a-zA-Z0-9._-]+$"
                   title="Nur Buchstaben, Zahlen, Punkt, Unterstrich und Bindestrich erlaubt">
        </div>

        <div class="mb-3">
            <label for="schema" class="form-label">Schema-URL</label>
            <input type="url" class="form-control" name="schema" id="schema" required
                   placeholder="https://www.openarchives.org/OAI/2.0/oai_dc.xsd"
                   title="Bitte eine gültige URL angeben">
        </div>

        <div class="mb-3">
            <label for="metadataNamespace" class="form-label">XML-Namespace</label>
            <input type="url" class="form-control" name="metadataNamespace" id="metadataNamespace" required
                   placeholder="https://www.openarchives.org/OAI/2.0/oai_dc/"
                   title="Bitte eine gültige URL angeben">
        </div>

        <div class="mb-3">
            <label for="comment" class="form-label">Kommentar (optional)</label>
            <textarea class="form-control" name="comment" id="comment" rows="2"
                      placeholder="z. B. Pflichteintrag für Dublin Core laut OAI-PMH-Spezifikation"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Speichern</button>
        <a class="btn btn-secondary" href="?controller=meta&action=list">Abbrechen</a>
    </form>
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

    // Autofokus auf erstes ungültiges Feld bei fehlschlagendem Submit
    document.getElementById('metaForm').addEventListener('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            const firstInvalid = this.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
</script>
