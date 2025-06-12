<form method="get" class="row g-3 mb-4">
    <input type="hidden" name="controller" value="Item">
    <input type="hidden" name="action" value="list">

    <div class="col-auto">
        <label for="repo" class="form-label">Repository:</label>
        <select name="repo" id="repo" class="form-select">
            <?php foreach ($repoList as $repo): ?>
                <option value="<?= $repo->getId() ?>" <?= $repo->getId() === $activeRepo ? 'selected' : '' ?>>
                    <?= htmlspecialchars($repo->getRepositoryName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-auto">
        <label for="limit" class="form-label">Items per page:</label>
        <select name="limit" id="limit" class="form-select">
            <?php foreach ($pagination->getAllowedLimits() as $option): ?>
                <option value="<?= $option ?>" <?= $option === $pagination->getLimit() ? 'selected' : '' ?>>
                    <?= $option ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-auto align-self-end">
        <button type="submit" class="btn btn-secondary">Apply</button>
    </div>
</form>