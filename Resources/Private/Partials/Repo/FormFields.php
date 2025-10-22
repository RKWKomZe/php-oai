<div class="form-group">
    <label for="repositoryName">Repository Name *</label>
    <input
            id="repositoryName"
            class="form-control"
            type="text"
            name="repositoryName"
            placeholder="e.g. My Repository"
            required
           value="<?= htmlspecialchars($oaiRepo->getRepositoryName()) ?>">
</div>

<div class="form-group">
    <label for="baseURL">Base URL *</label>
    <input
            id="baseURL"
            class="form-control"
            type="url"
            name="baseURL"
            placeholder="URL has to start with https and targets the correct endpoint"
            required
           value="<?= htmlspecialchars($oaiRepo->getBaseURL()) ?>">
</div>

<div class="form-group">
    <label for="protocolVersion">Protocol Version *</label>
    <select name="protocolVersion" id="protocolVersion" required class="form-control">
        <?php foreach (['2.0', '1.1', '1.0'] as $version): ?>
            <option value="<?= $version ?>" <?= $oaiRepo->getProtocolVersion() === $version ? 'selected' : '' ?>>
                <?= $version ?><?= $version === '2.0' ? ' (recommended)' : '' ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="adminEmails">Admin Email(s)</label>
    <input type="text" name="adminEmails" id="adminEmails" class="form-control"
           value="<?= htmlspecialchars($oaiRepo->getAdminEmails()) ?>">
</div>

<div class="form-group">
    <label for="earliestDatestamp">Earliest Datestamp</label>
    <input type="text" name="earliestDatestamp" id="earliestDatestamp" class="form-control"
           value="<?= htmlspecialchars($oaiRepo->getEarliestDatestamp() ?: '2000-01-01') ?>">
</div>

<div class="form-group">
    <label for="deletedRecord">Deleted Record *</label>
    <select name="deletedRecord" id="deletedRecord" required class="form-control">
        <?php foreach (['no', 'transient', 'persistent'] as $option): ?>
            <option value="<?= $option ?>" <?= $oaiRepo->getDeletedRecord() === $option ? 'selected' : '' ?>>
                <?= $option ?><?= $option === 'no' ? ' (recommended)' : '' ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="granularity">Granularity *</label>
    <select name="granularity" id="granularity" required class="form-control">
        <?php foreach (['YYYY-MM-DD', 'YYYY-MM-DDThh:mm:ssZ'] as $option): ?>
            <option value="<?= $option ?>" <?= $oaiRepo->getGranularity() === $option ? 'selected' : '' ?>>
                <?= $option ?><?= $option === 'YYYY-MM-DD' ? ' (recommended)' : '' ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label for="maxListSize">Max List Size *</label>
    <input type="number" name="maxListSize" id="maxListSize" class="form-control" min="0" placeholder="e.g. 100"
           value="<?= htmlspecialchars((string)$oaiRepo->getMaxListSize()) ?>" required>
</div>

<div class="form-group">
    <label for="tokenDuration">Token Duration (seconds) *</label>
    <input type="number" name="tokenDuration" id="tokenDuration" class="form-control" min="0" placeholder="e.g. 3600"
           value="<?= htmlspecialchars((string)$oaiRepo->getTokenDuration()) ?>" required>
</div>

<div class="form-group">
    <label for="description">Set Description (XML) *</label>
    <textarea
        class="form-control"
        name="description"
        id="description"
        placeholder="Paste your XML here"
        rows="5"
        required
    ><?= htmlspecialchars($oaiRepoDescription->getDescription()) ?></textarea>
</div>

<div class="form-group">
    <label for="comment">Comment</label>
    <textarea
        name="comment"
        id="comment"
        class="form-control"
        rows="3"><?= htmlspecialchars($oaiRepo->getComment()) ?></textarea>
</div>
