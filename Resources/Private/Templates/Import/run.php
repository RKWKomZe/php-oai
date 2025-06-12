<?php
$imported = $imported ?? 0;
$success = $success ?? false;
?>

<div class="container py-4">
    <h1>Import completed</h1>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($imported) ?> records have been successfully imported.
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            An error occurred during import.
        </div>
    <?php endif; ?>

    <a href="/index.php" class="btn btn-secondary mt-3">Back to homepage</a>
</div>
