<?php if ($pagination instanceof \RKW\OaiConnector\Utility\Pagination): ?>
    <?php if ($pagination->getTotalPages() > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center rkw-pagination">

                <!-- First Page -->
                <?php if ($pagination->getCurrentPage() > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $pagination->renderPageLink(1) ?>">&laquo;&laquo;</a>
                    </li>
                <?php endif; ?>

                <!-- Previous Page -->
                <?php if ($pagination->hasPrevious()): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $pagination->renderPageLink($pagination->getCurrentPage() - 1) ?>">
                            &laquo; Previous
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">&laquo; Previous</span>
                    </li>
                <?php endif; ?>

                <?php
                $current = $pagination->getCurrentPage();
                $total = $pagination->getTotalPages();
                $window = 2;
                $start = max(1, $current - $window);
                $end = min($total, $current + $window);

                if ($start > 1) {
                    echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $current ? 'active' : '' ?>">
                        <?php if ($i === $current): ?>
                            <span class="page-link"><?= $i ?></span>
                        <?php else: ?>
                            <a class="page-link" href="<?= $pagination->renderPageLink($i) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor;

                if ($end < $total) {
                    echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }
                ?>

                <!-- Next Page -->
                <?php if ($pagination->hasNext()): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $pagination->renderPageLink($pagination->getCurrentPage() + 1) ?>">
                            Next &raquo;
                        </a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled">
                        <span class="page-link">Next &raquo;</span>
                    </li>
                <?php endif; ?>

                <!-- Last Page -->
                <?php if ($pagination->getCurrentPage() < $pagination->getTotalPages()): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $pagination->renderPageLink($pagination->getTotalPages()) ?>">&raquo;&raquo;</a>
                    </li>
                <?php endif; ?>

            </ul>
        </nav>

        <!-- Direct input form -->
        <form class="pagination-select d-flex justify-content-center align-items-center mt-3" method="get" onsubmit="return false;">
            <label for="page-select" class="me-2">Seite auswählen:</label>
            <select id="page-select" class="form-select form-select-sm" style="width: auto;"
                    onchange="window.location.href = this.value;">
                <?php for ($i = 1; $i <= $pagination->getTotalPages(); $i++): ?>
                    <option value="<?= $pagination->renderPageLink($i) ?>" <?= $i === $pagination->getCurrentPage() ? 'selected' : '' ?>>
                        Seite <?= $i ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>

    <?php elseif ($pagination->getTotalPages() === 1): ?>
        <nav aria-label="Single-page navigation">
            <ul class="pagination justify-content-center rkw-pagination">
                <li class="page-item active">
                    <span class="page-link">1</span>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
