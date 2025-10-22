

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