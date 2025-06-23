<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink(
    'Repo',
    'edit',
    ['id' => $oaiRepo->getId()],
    'Bearbeiten',
    ['class' => 'btn btn-sm btn-secondary']);
?>