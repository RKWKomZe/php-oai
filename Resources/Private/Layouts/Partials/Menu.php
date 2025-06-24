<?php

use RKW\OaiConnector\Utility\MenuHelper;

    $current = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <img src="/Public/Img/logo.png" alt="RKW OAI" height="40">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink(null, null, 'Startseite'); ?>
                </li>
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink('Import', 'list', 'Import'); ?>
                </li>
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink('Item', 'list', 'Datensätze'); ?>
                </li>
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink('Repo', 'list', 'OAI Repositories'); ?>
                </li>
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink('Meta', 'list', 'OAI Meta'); ?>
                </li>
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink('Set', 'list', 'OAI Set'); ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
