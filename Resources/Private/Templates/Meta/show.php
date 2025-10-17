<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink('Meta', 'list', [], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>

<div class="col-md-6">
    <h1>Metadatenformat: <?= htmlspecialchars($oaiMeta->getMetadataPrefix()) ?></h1>

    <table class="table table-bordered">
        <tbody>
        <tr>
            <th scope="row">Repository</th>
            <td>
                <?php
                $repoName = $oaiMeta->getRepo();
                foreach ($repoList as $repoItem) {
                    if ($repoItem->getId() === $repoName) {
                        $repoName = $repoItem->getRepositoryName() . ' (' . $repoItem->getId() . ')';
                        break;
                    }
                }
                echo htmlspecialchars($repoName);
                ?>
            </td>
        </tr>
        <tr>
            <th scope="row">Metadaten-Prefix</th>
            <td><?= htmlspecialchars($oaiMeta->getMetadataPrefix()) ?></td>
        </tr>
        <tr>
            <th scope="row">Schema</th>
            <td><code><?= htmlspecialchars($oaiMeta->getSchema()) ?></code></td>
        </tr>
        <tr>
            <th scope="row">Namespace</th>
            <td><code><?= htmlspecialchars($oaiMeta->getMetadataNamespace()) ?></code></td>
        </tr>
        <tr>
            <th scope="row">Kommentar</th>
            <td><?= nl2br(htmlspecialchars($oaiMeta->getComment())) ?></td>
        </tr>
        <tr>
            <th scope="row">Letzte Änderung</th>
            <td><?= htmlspecialchars($oaiMeta->getUpdated()) ?></td>
        </tr>
        </tbody>
    </table>

</div>
