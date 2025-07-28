<?php

use RKW\OaiConnector\Utility\LinkHelper;

 ?>

<?php

echo LinkHelper::renderLink('Repo', 'list', ['repo' => $oaiRepo->getId()], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>
<?php // include __DIR__ . '/../../Partials/Repo/EditButton.php'; ?>
<?php // include __DIR__ . '/../../Partials/Repo/DeleteButton.php'; ?>

<h1>Repository Details</h1>

<table class="table table-bordered">
    <tbody>
    <tr>
        <th>ID</th>
        <td><?= htmlspecialchars($oaiRepo->getId()) ?></td>
    </tr>
    <tr>
        <th>Repository Name</th>
        <td><?= htmlspecialchars($oaiRepo->getRepositoryName()) ?></td>
    </tr>
    <tr>
        <th>Base URL</th>
        <td><?= htmlspecialchars($oaiRepo->getBaseURL()) ?></td>
    </tr>
    <tr>
        <th>Protocol Version</th>
        <td><?= htmlspecialchars($oaiRepo->getProtocolVersion()) ?></td>
    </tr>
    <tr>
        <th>Admin Emails</th>
        <td><?= htmlspecialchars($oaiRepo->getAdminEmails()) ?></td>
    </tr>
    <tr>
        <th>Earliest Datestamp</th>
        <td><?= htmlspecialchars($oaiRepo->getEarliestDatestamp()) ?></td>
    </tr>
    <tr>
        <th>Deleted Record</th>
        <td><?= htmlspecialchars($oaiRepo->getDeletedRecord()) ?></td>
    </tr>
    <tr>
        <th>Granularity</th>
        <td><?= htmlspecialchars($oaiRepo->getGranularity()) ?></td>
    </tr>
    <tr>
        <th>Max List Size</th>
        <td><?= $oaiRepo->getMaxListSize() !== null ? (int)$oaiRepo->getMaxListSize() : '—' ?></td>
    </tr>
    <tr>
        <th>Token Duration</th>
        <td><?= $oaiRepo->getTokenDuration() !== null ? (int)$oaiRepo->getTokenDuration() : '—' ?></td>
    </tr>
    <tr>
        <th>Updated</th>
        <td><?= htmlspecialchars($oaiRepo->getUpdated()) ?></td>
    </tr>
    <tr>
        <th>Comment</th>
        <td><?= nl2br(htmlspecialchars($oaiRepo->getComment())) ?></td>
    </tr>
    </tbody>
</table>

<?php if (!empty($oaiRepoDescription->getDescription())): ?>
    <h5>Set Description (XML)</h5>
    <pre class="bg-light p-2 border"><?= htmlspecialchars($oaiRepoDescription->getDescription()) ?></pre>
<?php endif; ?>

<p>
    <a href="<?= $this->linkHelper->create('Repo', 'list') ?>" class="btn btn-secondary">Back to list</a>
</p>
