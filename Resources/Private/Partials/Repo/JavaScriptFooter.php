<script>
    /**
     * Validation logic for repository forms (create + edit).
     * - Checks date format (granularity)
     * - Validates repository ID (only in create)
     * - Validates XML description syntax via DOMParser
     */
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        if (!form) return;

        const granularitySelect = document.getElementById('granularity');
        const datestampInput = document.getElementById('earliestDatestamp');
        const idInput = document.getElementById('id'); // only exists in create form
        const xmlInput = document.getElementById('description'); // XML textarea

        const regexDate = /^\d{4}-\d{2}-\d{2}$/;
        const regexDateTime = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/;

        // Helper: simple XML well-formedness check
        function isWellFormedXml(xmlString) {
            const parser = new DOMParser();
            const parsed = parser.parseFromString(xmlString, 'application/xml');
            const parserError = parsed.getElementsByTagName('parsererror');
            return parserError.length === 0;
        }

        // --- Repository ID validation (create form only) ---
        if (idInput) {
            const idFeedback = document.createElement('div');
            idFeedback.classList.add('invalid-feedback');
            idInput.insertAdjacentElement('afterend', idFeedback);

            idInput.addEventListener('blur', async () => {
                const repoId = idInput.value.trim();
                if (!repoId) return;

                try {
                    const response = await fetch(`?controller=repo&action=validateRepoId&repoId=${encodeURIComponent(repoId)}`);
                    const data = await response.json();

                    if (data.exists) {
                        idInput.classList.add('is-invalid');
                        idFeedback.textContent = 'This repository ID is already in use.';
                    } else {
                        idInput.classList.remove('is-invalid');
                        idFeedback.textContent = '';
                    }
                } catch (err) {
                    console.error('Error checking repository ID:', err);
                }
            });
        }

        // --- XML description validation ---
        if (xmlInput) {
            const xmlFeedback = document.createElement('div');
            xmlFeedback.classList.add('invalid-feedback');
            xmlInput.insertAdjacentElement('afterend', xmlFeedback);

            xmlInput.addEventListener('blur', () => {
                const value = xmlInput.value.trim();
                if (value === '') return; // required handled by HTML5

                if (!isWellFormedXml(value)) {
                    xmlInput.classList.add('is-invalid');
                    xmlFeedback.textContent = 'The XML fragment appears malformed or incomplete.';
                } else {
                    xmlInput.classList.remove('is-invalid');
                    xmlFeedback.textContent = '';
                }
            });
        }

        // --- Global form submit validation ---
        form.addEventListener('submit', async function (e) {
            let valid = true;

            // 1️⃣ Date validation
            const granularity = granularitySelect?.value;
            const datestamp = datestampInput?.value.trim();

            if (granularity === 'YYYY-MM-DD' && !regexDate.test(datestamp)) {
                alert("Please enter the 'Earliest Datestamp' in format YYYY-MM-DD.");
                valid = false;
            }

            if (granularity === 'YYYY-MM-DDThh:mm:ssZ' && !regexDateTime.test(datestamp)) {
                alert("Please enter the datestamp in full ISO format: YYYY-MM-DDThh:mm:ssZ");
                valid = false;
            }

            // 2️⃣ Repository ID re-check (only if field exists)
            if (idInput) {
                const repoId = idInput.value.trim();
                if (repoId) {
                    try {
                        const response = await fetch(`?controller=repo&action=validateRepoId&repoId=${encodeURIComponent(repoId)}`);
                        const data = await response.json();

                        if (data.exists) {
                            idInput.classList.add('is-invalid');
                            valid = false;
                            idInput.focus();
                        } else {
                            idInput.classList.remove('is-invalid');
                        }
                    } catch (err) {
                        console.error('Error checking repository ID:', err);
                    }
                }
            }

            // 3️⃣ XML well-formedness check
            if (xmlInput) {
                const value = xmlInput.value.trim();
                if (value && !isWellFormedXml(value)) {
                    xmlInput.classList.add('is-invalid');
                    valid = false;
                    xmlInput.focus();
                } else {
                    xmlInput.classList.remove('is-invalid');
                }
            }

            // Stop submit if anything failed
            if (!valid) e.preventDefault();
        });
    });
</script>