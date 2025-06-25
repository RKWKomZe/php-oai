<?php use RKW\OaiConnector\Utility\FlashMessageService;
use RKW\OaiConnector\Utility\LinkHelper; ?>

<h1>OAI Records</h1>

<div class="alert alert-info" role="alert">
    <strong>Note:</strong> This list shows records that have already been imported into the OAI system.

    <a class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="collapse" href="#importedInfoDetails" role="button" aria-expanded="false" aria-controls="importedInfoDetails">
        Show more...
    </a>

    <div class="collapse mt-2" id="importedInfoDetails">
        <p class="mb-1">
            The <strong>Identifier</strong> (e.g. <code>oai:shopware:01979cd996df759c8b3410c900c01a25</code>) is a unique OAI-PMH identifier derived from the original source.
            For Shopware products, it contains a hash based on the original product ID or UUID.
        </p>

        <p class="mb-1">
            To trace the origin of a record, check the identifier prefix (such as <code>shopware</code>) to determine the source system. If you manage the source integration, you may use the hash logic to reverse-map the identifier.
        </p>

        <p class="mb-0">
            The <strong>timestamp</strong> indicates when the record was imported into the OAI system. The <strong>metadata format</strong> column (e.g. <code>oai_dc</code>) shows which format was used for harvesting.
        </p>
    </div>
</div>

<?php include __DIR__ . '/../../Partials/ListFilter.php'; ?>

<?php if (empty($itemList)): ?>
    <div class="alert alert-info">No records found for the selected repository.</div>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Identifier</th>
            <th>Datestamp</th>
            <th>Prefix</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($itemList as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item->getIdentifier()) ?></td>
                <td><?= htmlspecialchars($item->getDatestamp()) ?></td>
                <td><?= htmlspecialchars($item->getMetadataPrefix()) ?></td>
                <td>
                    <?php if ($item->isDeleted()): ?>
                        <span class="badge bg-danger">Deleted</span>
                    <?php else: ?>
                        <span class="badge bg-success">Active</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    echo LinkHelper::renderLink(
                            'Item',
                            'show',
                            [
                                'id' => $item->getIdentifier(),
                                'repo' => $activeRepo,
                                'returnTo' => $_SERVER['REQUEST_URI']
                            ],
                            'Details',
                            ['class' => 'btn btn-sm btn-secondary']);
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../../Partials/Pagination.php'; ?>
