<?php

use RKW\OaiConnector\Utility\LinkHelper;

echo LinkHelper::renderLink(
    'Repo',
    'delete',
    ['id' => $oaiRepo->getId()],
    'Löschen',
    [
        'class' => 'btn btn-sm btn-danger',
        'onclick' => 'return confirm("Are you sure you want to delete this record?")'
    ]);
?>