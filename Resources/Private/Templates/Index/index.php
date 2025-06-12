<?php
// declare variables
?>

<div class="container py-4">
    <h1>Welcome to the OAI Connector</h1>
    <p class="lead">This interface allows you to import and manage product records from Shopware via OAI-PMH.</p>

    <a href="/index.php?controller=Import&action=run" class="btn btn-primary">Run Shopware Import</a>

</div>


<!--

<?php session_start(); ?>


    <div class="container py-4">
        <?php include __DIR__ . '/fragment/header.php'; ?>
        <h1 class="mb-4">OAI-Shopware Integration</h1>

        <?php if (!empty($_SESSION['messages'])): ?>
            <?php foreach ($_SESSION['messages'] as $msg): ?>
                <div class="alert alert-<?= htmlspecialchars($msg['type']) ?>">
                    <?= htmlspecialchars($msg['text']) ?>
                </div>
            <?php endforeach; unset($_SESSION['messages']); ?>
        <?php endif; ?>

        <a href="import_shopware.php" class="btn btn-primary">
            <i class="fas fa-download"></i> Shopware-Produkte importieren
        </a>
    </div>

<?php include __DIR__ . '/fragment/footer.php'; ?>
-->
