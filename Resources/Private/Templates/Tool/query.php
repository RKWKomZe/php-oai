<?php
$today = (new DateTime())->format('Y-m-d');
$oneMonthAgo = (new DateTime('-1 month'))->format('Y-m-d');
?>

<h1 class="mb-4">Endpoint Queries</h1>

<div class="alert alert-info" role="alert">
    <strong>Note:</strong> Because the until parameter is interpreted as midnight at the start of the given date, records added later that day may be excluded. To retrieve them, use the next day as your until date.
</div>

<div class="row">
    <div class="col-md-6">
        <form id="oaiQueryForm" method="get" action="/index.php" target="_blank" class="border p-3 rounded bg-light">
            <input type="hidden" name="controller" value="endpoint">
            <input type="hidden" name="action" value="handle">

            <select id="repo" name="repo" class="form-select mb-3">
                <?php foreach ($repoList as $repo): ?>
                    <option value="<?= htmlspecialchars($repo->getId()) ?>">
                        <?= htmlspecialchars($repo->getRepositoryName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>


            <div class="mb-3">
                <label for="set" class="form-label">Set (optional)</label>
                <select id="set" name="set" class="form-select">
                    <!-- Wird dynamisch befüllt -->
                </select>
            </div>

            <div class="mb-3">
                <label for="verb" class="form-label">OAI Verb</label>
                <select class="form-select" id="verb" name="verb" required>
                    <option value="Identify">Identify</option>
                    <option value="ListMetadataFormats">ListMetadataFormats</option>
                    <option value="ListSets">ListSets</option>
                    <option value="ListIdentifiers">ListIdentifiers</option>
                    <option value="ListRecords">ListRecords</option>
                    <option value="GetRecord">GetRecord</option>
                </select>
            </div>

            <div class="mb-3 form-group field-group" data-field="identifier">
                <label for="identifier">Identifier</label>
                <input type="text" class="form-control" name="identifier" id="identifier">
            </div>


            <div class="mb-3 field-group" data-field="metadataPrefix">
                <label for="metadataPrefix" class="form-label">Metadata Prefix</label>
                <select class="form-select" id="metadataPrefix" name="metadataPrefix">
                    <option value="marcxml">MARCXML</option>
                    <option value="oai_dc">Dublin Core (oai_dc)</option>
                    <!--<option value="marc21">MARC21</option>-->
                </select>
            </div>

            <div class="mb-3 field-group" data-field="from">
                <label for="from" class="form-label">From (optional)</label>
                <input id="from" type="date" name="from" class="form-control" value="<?= htmlspecialchars($_GET['from'] ?? $oneMonthAgo) ?>">
            </div>

            <div class="mb-3 field-group" data-field="until">
                <label for="until" class="form-label">Until (optional)</label>
                <input id="until" type="date" name="until" class="form-control" value="<?= htmlspecialchars($_GET['until'] ?? $today) ?>">
            </div>

            <button type="submit" class="btn btn-primary">Send Test Request</button>
        </form>
    </div>
    <div class="col-md-6">
        <div class="alert alert-info d-flex align-items-start" role="alert">
            <i class="bi bi-info-circle-fill me-2 mt-1" aria-hidden="true"></i>
            <div>
                <strong>OAI-PMH Test Interface:</strong>
                <p class="mb-1">
                    This form allows you to simulate real-world OAI-PMH requests, just like those made by external data harvesters such as the <abbr title="German National Library">DNB</abbr>.
                </p>
                <p class="mb-1">
                    It uses the same interface as publicly accessible endpoints, based on the Open Archives Initiative Protocol for Metadata Harvesting (OAI-PMH).
                </p>
                <p class="mb-1">
                    Example query endpoint:
                    <code>https://your-oai-server.com/index.php?controller=endpoint&action=handle&repo=demo&verb=Identify</code>
                </p>
                <ul class="mb-1">
                    <li><strong>Repository:</strong> Defines which OAI source to query</li>
                    <li><strong>Verb:</strong> Selects the type of OAI-PMH operation (e.g. Identify, ListRecords, etc.)</li>
                    <li><strong>Metadata Prefix:</strong> Specifies the format (e.g. <code>oai_dc</code>, <code>marcxml</code>)</li>
                    <li><strong>Date Range:</strong> Limits results to a given period (optional)</li>
                    <li><strong>Set:</strong> Filters data within the selected repository (optional)</li>
                </ul>
                <p class="mb-0">
                    Submitted queries will open in a new tab and return XML responses as seen by any external service querying this OAI endpoint.
                </p>
            </div>
        </div>
    </div>
</div>


<script>

    const setsByRepo = <?= json_encode($setsByRepo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const repoSelect = document.getElementById('repo');
        const setSelect = document.getElementById('set');

        function populateSets(repo) {
            const sets = setsByRepo[repo] || [];
            setSelect.innerHTML = '';

            // Add empty option (optional selection)
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = '-- no set selected --';
            setSelect.appendChild(emptyOption);

            // Add all sets for the repo
            sets.forEach(set => {
                const opt = document.createElement('option');
                opt.value = set.setSpec;
                opt.textContent = `${set.setName} (${set.setSpec})`;
                setSelect.appendChild(opt);
            });
        }

        // Initialfüllung
        populateSets(repoSelect.value);

        // On change
        repoSelect.addEventListener('change', () => {
            populateSets(repoSelect.value);
        });

        const allowedFieldsByVerb = {
            Identify: [],
            ListSets: [],
            ListMetadataFormats: ['identifier'],
            ListIdentifiers: ['metadataPrefix', 'from', 'until', 'set'],
            ListRecords: ['metadataPrefix', 'from', 'until', 'set'],
            GetRecord: ['identifier', 'metadataPrefix']
        };


        const verbSelect = document.getElementById('verb');
        const allFieldGroups = document.querySelectorAll('.field-group');


        function updateVisibleFields() {
            const verb = verbSelect.value;

            allFieldGroups.forEach(group => {
                const field = group.dataset.field;
                const input = group.querySelector('[name], select, input');

                if (allowedFieldsByVerb[verb]?.includes(field)) {
                    group.style.display = '';
                    input.setAttribute('name', field);
                } else {
                    group.style.display = 'none';
                    input.removeAttribute('name');
                }
            });
        }


        verbSelect.addEventListener('change', updateVisibleFields);
        updateVisibleFields(); // initial on page load



        const form = document.getElementById('oaiQueryForm');

        form.addEventListener('submit', function () {
            const verb = document.getElementById('verb').value;

            // Entferne unzulässige Felder je nach Verb
            if (!['ListIdentifiers', 'ListRecords', 'GetRecord'].includes(verb)) {
                document.getElementById('metadataPrefix')?.removeAttribute('name');
            }

            if (!['ListIdentifiers', 'ListRecords'].includes(verb)) {
                document.getElementById('from')?.removeAttribute('name');
                document.getElementById('until')?.removeAttribute('name');
                document.getElementById('set')?.removeAttribute('name');
            }

            if (verb !== 'GetRecord') {
                document.getElementById('identifier')?.removeAttribute('name');
            }

            // Optional: Falls Set leer, auch entfernen
            const setSelect = document.getElementById('set');
            if (setSelect && setSelect.value === '') {
                setSelect.removeAttribute('name');
            }
        });


    });

</script>