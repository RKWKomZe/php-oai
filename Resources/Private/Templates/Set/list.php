<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">OAI Sets</h1>
    <a class="btn btn-secondary" href="?controller=set&action=new">Neues Set anlegen</a>
</div>

<div class="alert alert-info">
    <strong>What are OAI Sets?</strong> Sets allow you to organize your repository into logical groups — such as by content type or collection.
    This helps external services selectively harvest only relevant records.
    <a href="https://www.openarchives.org/OAI/openarchivesprotocol.html#Set" target="_blank" class="ms-2">Learn more</a>
</div>

<?php

use RKW\OaiConnector\Utility\LinkHelper;

if (!empty($setList)) : ?>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Repository</th>
            <th>Set Spec</th>
            <th>Set Name</th>
            <th>Rang</th>
            <th>Letzte Änderung</th>
            <th>Aktionen</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($setList as $record) : ?>
            <tr>
                <td><?= htmlspecialchars($record->getRepo()) ?></td>
                <td><?= htmlspecialchars($record->getSetSpec()) ?></td>
                <td><?= htmlspecialchars($record->getSetName()) ?></td>
                <td><?= htmlspecialchars($record->getRank()) ?></td>
                <td><?= htmlspecialchars($record->getUpdated()) ?></td>
                <td>
                    <?php
                    echo LinkHelper::renderLink(
                        'Set',
                        'show',
                        ['repo' => $record->getRepo(), 'spec' => $record->getSetSpec()],
                        'Details',
                        ['class' => 'btn btn-sm btn-secondary']
                    );
                    ?>
                    <?php
                    echo LinkHelper::renderLink(
                        'Set',
                        'edit',
                        ['repo' => $record->getRepo(), 'spec' => $record->getSetSpec()],
                        'Bearbeiten',
                        ['class' => 'btn btn-sm btn-secondary']
                    );
                    ?>
                    <?php
                    echo LinkHelper::renderLink(
                        'Set',
                        'delete',
                        ['repo' => $record->getRepo(), 'spec' => $record->getSetSpec()],
                        'Löschen',
                        [
                            'class' => 'btn btn-sm btn-danger',
                            'onclick' => 'return confirm("Are you sure you want to delete this set?")'
                        ]
                    );
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>Keine Sets gefunden.</p>
<?php endif; ?>
