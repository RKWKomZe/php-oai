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
                    <?php echo MenuHelper::renderMenuLink(null, null, 'Start'); ?>
                </li>
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink('Import', 'list', 'Import'); ?>
                </li>
                <li class="nav-item">
                    <?php echo MenuHelper::renderMenuLink('Item', 'list', 'Records'); ?>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="oaiDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        OAI Data
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="oaiDropdown">
                        <li>
                            <?php echo MenuHelper::renderMenuLink(
                                'repo',
                                'list',
                                'OAI Repositories',
                                [],
                                [],
                                'dropdown-item'
                            ); ?>
                        </li>
                        <li>
                            <?php echo MenuHelper::renderMenuLink(
                                'meta',
                                'list',
                                'OAI Meta',
                                [],
                                [],
                                'dropdown-item'
                            ); ?>
                        </li>
                        <li>
                            <?php echo MenuHelper::renderMenuLink(
                                'set',
                                'list',
                                'OAI Set',
                                [],
                                [],
                                'dropdown-item'
                            ); ?>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="toolDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        Tools
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="toolDropdown">
                        <li>
                            <?php echo MenuHelper::renderMenuLink(
                                'tool',
                                'query',
                                'Endpoint Queries',
                                [],
                                [],
                                'dropdown-item'
                            ); ?>
                        </li>
                        <li>
                            <?php echo MenuHelper::renderMenuLink(
                                'tool',
                                'fullImport',
                                'Full Import',
                                [],
                                [],
                                'dropdown-item'
                            ); ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
