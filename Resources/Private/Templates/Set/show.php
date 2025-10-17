<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink('Set', 'list', [], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>

<div class="col-md-6">
    <h1>Set: <?= htmlspecialchars($oaiSet->getSetSpec()) ?></h1>

    <table class="table table-bordered">
        <tbody>
            <tr>
                <th scope="row">Repository</th>
                <td>
                    <?php
                    $repoName = $oaiSet->getRepo();
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
                <th scope="row">Set Spec</th>
                <td><?= htmlspecialchars($oaiSet->getSetSpec()) ?></td>
            </tr>
            <tr>
                <th scope="row">Set Name</th>
                <td><?= htmlspecialchars($oaiSet->getSetName()) ?></td>
            </tr>
            <tr>
                <th scope="row">Rank</th>
                <td><?= htmlspecialchars($oaiSet->getRank()) ?></td>
            </tr>
            <tr>
                <th scope="row">Comment</th>
                <td><?= nl2br(htmlspecialchars($oaiSet->getComment() ?? '')) ?></td>

            </tr>
            <tr>
                <th scope="row">Last Updated</th>
                <td><?= htmlspecialchars($oaiSet->getUpdated()) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($oaiSetDescription->getSetDescription())): ?>
        <h5>Set Description (XML)</h5>
        <pre class="bg-light p-2 border"><?= htmlspecialchars($oaiSetDescription->getSetDescription()) ?></pre>
    <?php endif; ?>

</div>
