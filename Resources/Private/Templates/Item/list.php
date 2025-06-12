<?php use RKW\OaiConnector\Utility\FlashMessageService;
use RKW\OaiConnector\Utility\LinkHelper; ?>

<h1>OAI Records</h1>

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
