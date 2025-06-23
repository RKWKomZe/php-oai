<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">OAI Repositories</h1>
    <a class="btn btn-secondary" href="?controller=repo&action=new">Neues Repository anlegen</a>
</div>

<div class="alert alert-info">
    <strong>What is an OAI repository?</strong> A repository is a logical unit of published metadata that can be accessed via the OAI-PMH protocol.
    It defines the base URL and serves as the entry point for harvesting metadata records.
    <a href="https://www.openarchives.org/OAI/openarchivesprotocol.html#Repository" target="_blank" class="ms-2">Learn more</a>
</div>



<?php use RKW\OaiConnector\Utility\LinkHelper;

if (empty($repoList)): ?>
    <div class="alert alert-info">No records found for the selected repository.</div>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Identifier</th>
            <th>Base URL</th>
            <th>Version</th>
            <th>Admin Email</th>
            <th>Aktion</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($repoList as $oaiRepo): ?>
        <tr>
            <td><?= htmlspecialchars($oaiRepo->getId()) ?></td>
            <td><?= htmlspecialchars($oaiRepo->getBaseUrl()) ?></td>
            <td><?= htmlspecialchars($oaiRepo->getProtocolVersion()) ?></td>
            <td><?= htmlspecialchars($oaiRepo->getAdminEmails()) ?></td>
            <td>
                <?php
                echo LinkHelper::renderLink(
                    'Repo',
                    'show',
                    ['id' => $oaiRepo->getId()],
                    'Details',
                    ['class' => 'btn btn-sm btn-secondary']);
                ?>
                <?php include __DIR__ . '/../../Partials/Repo/EditButton.php'; ?>
                <?php include __DIR__ . '/../../Partials/Repo/DeleteButton.php'; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>