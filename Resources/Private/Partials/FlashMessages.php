<?php

use RKW\OaiConnector\Utility\FlashMessage;

$flashMessages = FlashMessage::getAll();

$icons = [
    'primary'   => 'bi-info-circle-fill',
    'secondary' => 'bi-info-circle',
    'success'   => 'bi-check-circle-fill',
    'danger'    => 'bi-exclamation-triangle-fill',
    'warning'   => 'bi-exclamation-circle-fill',
    'info'      => 'bi-info-circle-fill',
    'light'     => 'bi-lightbulb',
    'dark'      => 'bi-moon-stars-fill'
];

if (!empty($flashMessages)): ?>
    <div class="container mt-3">
        <?php foreach ($flashMessages as $message):
            $type = htmlspecialchars($message['type']);
            $icon = $icons[$type] ?? 'bi-info-circle';
            ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi <?= $icon ?> me-2" aria-hidden="true"></i>
                <div><?= htmlspecialchars($message['text']) ?></div>
                <div class="fadeout-timer"></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
