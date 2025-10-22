<div class="mb-3">
    <label for="setName" class="form-label">Set Name *</label>
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
    <textarea
        id="comment"
        class="form-control"
        name="comment"
        rows="2"
        placeholder="e.g. Required for OpenAIRE compatibility"
    ><?= htmlspecialchars($oaiSet->getComment() ?? '') ?></textarea>
</div>

<div class="mb-3">
    <label for="setDescription" class="form-label">Set Description (XML) *</label>
    <textarea
        id="setDescription"
        class="form-control"
        name="setDescription"
        rows="5"
        placeholder="Paste XML here if needed"
        required><?= htmlspecialchars(trim($oaiSetDescription?->getSetDescription()) ?? '') ?></textarea>
</div>