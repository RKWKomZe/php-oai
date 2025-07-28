<h1>Edit metadata format</h1>

<div class="col-md-6">
    <form method="post" action="?controller=meta&action=update" id="metaForm" novalidate>
        <input type="hidden" name="repo" value="<?= htmlspecialchars($oaiMeta->getRepo()) ?>">
        <input type="hidden" name="metadataPrefix" value="<?= htmlspecialchars($oaiMeta->getMetadataPrefix()) ?>">

        <div class="mb-3">
            <label class="form-label">Repository</label>
            <select class="form-select">
                <?php foreach ($repoList as $repoItem): ?>
                    <option value="<?= htmlspecialchars($repoItem->getId()) ?>">
                        <?= htmlspecialchars($repoItem->getRepositoryName() . ' (' . $repoItem->getId() . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Repository kann nicht nachträglich geändert werden.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Metadaten-Prefix</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($oaiMeta->getMetadataPrefix()) ?>" readonly>
            <div class="form-text">Prefix kann nicht nachträglich geändert werden.</div>
        </div>

        <div class="mb-3">
            <label for="schema" class="form-label">Schema-URL</label>
            <input type="url" class="form-control" name="schema" id="schema" required
                   value="<?= htmlspecialchars($oaiMeta->getSchema()) ?>"
                   placeholder="https://www.openarchives.org/OAI/2.0/oai_dc.xsd">
        </div>

        <div class="mb-3">
            <label for="metadataNamespace" class="form-label">XML-Namespace</label>
            <input type="url" class="form-control" name="metadataNamespace" id="metadataNamespace" required
                   value="<?= htmlspecialchars($oaiMeta->getMetadataNamespace()) ?>"
                   placeholder="https://www.openarchives.org/OAI/2.0/oai_dc/">
        </div>

        <div class="mb-3">
            <label for="comment" class="form-label">Kommentar (optional)</label>
            <textarea class="form-control" name="comment" id="comment" rows="2"
                      placeholder="z. B. Dublin Core Standardformat"><?= htmlspecialchars($oaiMeta->getComment()) ?></textarea>
        </div>

        <a class="btn btn-secondary" href="?controller=meta&action=list">Abbrechen</a>
        <button type="submit" class="btn btn-primary">Speichern</button>
    </form>
</div>

<script>
    // Fokus auf erstes invalides Feld bei fehlschlagender Validierung
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
