<h1>Repositories</h1>
<a href="?controller=repo&action=new">Neues Repository anlegen</a>

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
        <?php foreach ($repoList as $repo): ?>
        <tr>
            <td><?= htmlspecialchars($repo->getId()) ?></td>
            <td><?= htmlspecialchars($repo->getBaseUrl()) ?></td>
            <td><?= htmlspecialchars($repo->getProtocolVersion()) ?></td>
            <td><?= htmlspecialchars($repo->getAdminEmails()) ?></td>
            <td>
                <?php
                echo LinkHelper::renderLink(
                    'Repo',
                    'show',
                    [
                        'id' => $repo->getId(),
                        'returnTo' => $_SERVER['REQUEST_URI']
                    ],
                    'Details',
                    ['class' => 'btn btn-sm btn-secondary']);
                ?>
                <?php
                echo LinkHelper::renderLink(
                    'Repo',
                    'edit',
                    [
                        'id' => $repo->getId(),
                        'returnTo' => $_SERVER['REQUEST_URI']
                    ],
                    'Bearbeiten',
                    ['class' => 'btn btn-sm btn-secondary']);
                ?>
                <?php
                echo LinkHelper::renderLink(
                    'Repo',
                    'delete',
                    [
                        'id' => $repo->getId(),
                        'returnTo' => $_SERVER['REQUEST_URI']
                    ],
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
<?php endif; ?>