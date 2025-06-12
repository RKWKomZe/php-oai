<?php
// === /Classes/View/Layout/DefaultLayout.php ===

function renderLayout(string $viewPath, array $data = []): void
{
    extract($data);

    include __DIR__ . '/Partials/Header.php'; ?>
    <div class="container mt-4">
    <?php include $viewPath; ?>
    </div><?php

    include __DIR__ . '/Partials/Footer.php';
}
