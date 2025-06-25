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

        <div class="mb-3">
            <label for="setName" class="form-label">Set Name</label>
            <input type="text" class="form-control" name="setName" id="setName" required
                   placeholder="e.g. Publications or Research Data"
                   value="<?= htmlspecialchars($oaiSet->getSetName() ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="rank" class="form-label">Rank</label>
            <input type="number" class="form-control" name="rank" id="rank" min="0" required
                   value="<?= htmlspecialchars($oaiSet->getRank()) ?>">
        </div>

        <div class="mb-3">
            <label for="comment" class="form-label">Comment (optional)</label>
            <textarea class="form-control" name="comment" id="comment" rows="2"
                      placeholder="e.g. Required for OpenAIRE compatibility"><?= htmlspecialchars($oaiSet->getComment() ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="setDescription" class="form-label">Set Description (XML)</label>
            <textarea class="form-control" name="setDescription" id="setDescription" rows="5"
                      placeholder="Paste XML here if needed"><?= htmlspecialchars($oaiSetDescription?->getSetDescription() ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save changes</button>
        <a class="btn btn-secondary" href="?controller=set&action=list">Cancel</a>
    </form>
</div>

<script>
    document.getElementById('setForm').addEventListener('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            const firstInvalid = this.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });

</script>
