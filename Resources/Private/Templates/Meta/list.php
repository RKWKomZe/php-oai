
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">OAI Metadatenformate</h1>
    <a class="btn btn-secondary" href="?controller=meta&action=new">Neues Metadatenformat anlegen</a>
</div>

<div class="alert alert-info">
    <strong>What are metadata formats?</strong> OAI-PMH requires records to be delivered in standardized XML formats.
    The most common is <code>oai_dc</code> (Dublin Core), but repositories can offer multiple formats to meet different needs.
    <a href="https://www.openarchives.org/OAI/openarchivesprotocol.html#Metadata" target="_blank" class="ms-2">Learn more</a>
</div>


<?php use RKW\OaiConnector\Utility\LinkHelper;

if (!empty($metaList)) : ?>
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Repository</th>
            <th>Prefix</th>
            <th>Schema</th>
            <th>Namespace</th>
            <th>Letzte Änderung</th>
            <th>Aktionen</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($metaList as $record) : ?>
            <tr>
                <td><?= htmlspecialchars($record->getRepo()) ?></td>
                <td><?= htmlspecialchars($record->getMetadataPrefix()) ?></td>
                <td><?= htmlspecialchars($record->getSchema()) ?></td>
                <td><?= htmlspecialchars($record->getMetadataNamespace()) ?></td>
                <td><?= htmlspecialchars($record->getUpdated()) ?></td>
                <td>
                    <?php
                    echo LinkHelper::renderLink(
                        'Meta',
                        'show',
                        ['prefix' => $record->getMetadataPrefix(), 'repo' => $record->getRepo()],
                        'Details',
                        ['class' => 'btn btn-sm btn-secondary']);
                    ?>
                    <?php
                    echo LinkHelper::renderLink(
                        'Meta',
                        'edit',
                        ['prefix' => $record->getMetadataPrefix(), 'repo' => $record->getRepo()],
                        'Bearbeiten',
                        ['class' => 'btn btn-sm btn-secondary']);
                    ?>
                    <?php
                    echo LinkHelper::renderLink(
                        'Meta',
                        'delete',
                        ['prefix' => $record->getMetadataPrefix(), 'repo' => $record->getRepo()],
                        'Löschen',
                        [
                            'class' => 'btn btn-sm btn-danger',
                            'onclick' => 'return confirm("Are you sure you want to delete this record?")'
                        ]);
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p>Keine Metadatenformate gefunden.</p>
<?php endif; ?>
