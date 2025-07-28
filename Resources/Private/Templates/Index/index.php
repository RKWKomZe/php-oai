<?php
// declare variables

$oaiFacts = [
    "OAI‑PMH stands for Open Archives Initiative Protocol for Metadata Harvesting — first released in 2001, version 2.0 came out in June 2002.",
    "The protocol defines six basic 'verbs': Identify, ListMetadataFormats, ListIdentifiers, ListRecords, GetRecord and ListSets.",
    "Dublin Core (oai_dc) is the mandatory minimum metadata format supported by all OAI‑PMH repositories.",
    "OAI‑PMH works over HTTP and uses XML exclusively for requests and responses.",
    "The protocol supports selective harvesting using datestamp ranges and 'sets' (groupings) of records.",
    "A record identifier must be globally unique, commonly expressed as a URI or UUID.",
    "Large systems like arXiv, DSpace, Fedora, OJS, and Koha all provide OAI‑PMH interfaces.",
    "Many major harvesters use OAI‑PMH: Wikimedia, BASE, OAIster, Europeana, and the German National Library.",
    "Google briefly supported OAI‑PMH in its Sitemap schema, but dropped it in 2008.",
    "OAI‑PMH supports pagination of large result sets via 'resumptionTokens'.",
    "Error conditions (e.g. badArgument) are reported in XML, not via HTTP status codes.",
    "A container <about> allows repositories to include additional metadata (like provenance) for each record.",
    "The protocol is designed as a low-barrier interoperability framework, promoting widespread adoption.",
    "OAI‑PMH was initiated in 1999 at the 'Santa Fe Convention', bringing together repositories and archives.",
    "The initiative was co-founded by Herbert Van de Sompel and Carl Lagoze, supported by NSF, Sloan Foundation, DLF, CNI, and Mellon.",
    "Herbert Van de Sompel (born 1957) is a Belgian librarian and computer scientist who played a pivotal role in OAI‑PMH, OpenURL, ORE, and the Memento Project.",
    "In 2006 Herbert Van de Sompel received the SPARC Innovator Award for establishing OAI and the OpenURL framework.",
    "Van de Sompel worked at Los Alamos National Lab until 2018 and later joined DANS in the Netherlands as Chief Innovation Officer.",
    "The OAI secretariat at Cornell University in 2000 refined the protocol, resulting in OAI‑PMH version 1.0 and later 2.0.",
    "Carl Lagoze is another co‑founder of OAI and co‑editor of the original protocol specification.",
    "OAI‑PMH influenced later protocols like OAI‑ORE and ResourceSync, promoting richer metadata exchange.",
    "OAI workshops began at CERN and Geneva in 2001, and the biennial 'Geneva Workshop' continues to discuss scholarly communication.",
    "OpenAIRE harvests repositories weekly using OAI‑PMH, including DataCite-compliant metadata via oai_datacite.",
    "OpenAIRE encourages a setSpec value of openaire_data to identify dataset-relevant records.",
    "Europeana, BASE, DNB, OAIster, and others often rely on OAI sets to segment record harvesting.",
    "ResourceSync is a complementary protocol that enables file-level and resource synchronization alongside metadata.",
    "PostgreSQL, MySQL, DSpace, Fedora, EPrints and DataCite all have built-in OAI‑PMH support.",
    "Harvester tools like MarcEdit, R Harvest, and OCLC’s automation tools are commonly used with OAI‑PMH."

];

$factOfTheDay = $oaiFacts[date('z') % count($oaiFacts)];
?>



<div class="container py-4">

    <h1>Welcome to the OAI Connector</h1>

    <p class="lead">This interface allows you to import and manage product records from Shopware via OAI-PMH.</p>

    <?php if (!empty($factOfTheDay)) : ?>
        <div class="alert alert-info d-flex align-items-start p-3 mt-4 border-info rounded shadow-sm small" role="alert">
            <i class="bi bi-lightbulb-fill me-3 fs-4 text-primary" aria-hidden="true"></i>
            <div>
                <strong>Did you know?</strong><br>
                <?= htmlspecialchars($factOfTheDay) ?>
            </div>
        </div>
    <?php endif; ?>

    <!--
    <a href="/index.php?controller=Import&action=run" class="btn btn-primary">Run Shopware Import</a>
    -->

</div>

<h3 class="mb-4">OAI Update Log</h3>


<table class="table table-sm table-bordered table-striped">
    <thead class="table-light">
    <tr>
        <th>#</th>
        <th>Repository</th>
        <th>Start</th>
        <th>End</th>
        <th>Status</th>
        <th>Inserted</th>
        <th>Deleted</th>
        <th>Error</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($logs as $log): ?>
        <tr class="<?= $log['error'] ? 'table-danger' : '' ?>">
            <td><?= (int)$log['id'] ?></td>
            <td><?= htmlspecialchars($log['repo']) ?></td>
            <td><?= htmlspecialchars($log['date_start'] ?? '-') ?></td>
            <td><?= htmlspecialchars($log['date_end'] ?? '-') ?></td>
            <td><?= htmlspecialchars($log['status'] ?? '-') ?></td>
            <td><?= (int)$log['meta_inserted'] ?></td>
            <td><?= (int)$log['meta_deleted'] ?></td>
            <td><?= $log['error'] ? '⚠️' : '🟩' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>


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
