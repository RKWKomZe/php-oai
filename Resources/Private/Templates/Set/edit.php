<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Set</h1>
</div>

<div class="col-md-6">
    <form method="post" action="?controller=set&action=update" id="setForm" novalidate>
        <input type="hidden" name="originalRepo" value="<?= htmlspecialchars($oaiSet->getRepo()) ?>">
        <input type="hidden" name="originalSetSpec" value="<?= htmlspecialchars($oaiSet->getSetSpec()) ?>">

        <div class="mb-3">
            <label for="repo" class="form-label">Repository</label>
            <select class="form-select" name="repo" id="repo" required>
                <option value="" disabled hidden>Please select...</option>
                <?php foreach ($repoList as $repoItem): ?>
                    <option value="<?= htmlspecialchars($repoItem->getId()) ?>"
                        <?= ($repoItem->getId() === $oaiSet->getRepo()) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($repoItem->getRepositoryName() . ' (' . $repoItem->getId() . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="setSpec" class="form-label">Set Spec</label>
            <input type="text" class="form-control" name="setSpec" id="setSpec" required
                   placeholder="e.g. pubs or data" pattern="^[a-zA-Z0-9._-]+$"
                   title="Only letters, numbers, dot, underscore and hyphen are allowed"
                   value="<?= htmlspecialchars($oaiSet->getSetSpec()) ?>">
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
