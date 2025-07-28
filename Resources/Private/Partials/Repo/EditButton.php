<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink(
    'Repo',
    'edit',
    ['id' => $oaiRepo->getId()],
    'Edit',
    ['class' => 'btn btn-sm btn-secondary']);
?>