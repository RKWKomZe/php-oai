<?php

use RKW\OaiConnector\Utility\FormatXml;
use RKW\OaiConnector\Utility\LinkHelper;

$returnTo = $_GET['returnTo'] ?? null;
 ?>

<h1>Record Details</h1>

<?php

echo $returnTo
    ? '<a href="' . htmlspecialchars($returnTo) . '" class="btn btn-sm btn-outline-secondary mb-3">&larr; Back to list</a>'
    : LinkHelper::renderLink('Item', 'list', ['repo' => $item->getRepo()], '&larr; Back to list', ['class' => 'btn btn-sm btn-outline-secondary mb-3']);

?>

<table class="table table-bordered">
    <tr>
        <th>Identifier</th>
        <td><?= htmlspecialchars($item->getIdentifier()) ?></td>
    </tr>
    <tr>
        <th>Datestamp</th>
        <td><?= htmlspecialchars($item->getDatestamp()) ?></td>
    </tr>
    <tr>
        <th>Prefix</th>
        <td><?= htmlspecialchars($item->getMetadataPrefix()) ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td>
            <?php if ($item->isDeleted()): ?>
                <span class="badge bg-danger">Deleted</span>
            <?php else: ?>
                <span class="badge bg-success">Active</span>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Created</th>
        <td><?= htmlspecialchars($item->getCreated()) ?></td>
    </tr>
    <tr>
        <th>Updated</th>
        <td><?= htmlspecialchars($item->getUpdated()) ?></td>
    </tr>
    <tr>
        <th>Metadata (XML)</th>
        <td>
            <pre class="bg-light p-2" style="white-space:pre; overflow:auto;"><code class="language-xml"><?= FormatXml::formatXmlForDisplay((string)$item->getMetadata()); ?></code></pre>
        </td>
    </tr>
</table>
