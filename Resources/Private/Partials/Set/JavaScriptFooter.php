<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('setForm');
        const xmlInput = document.getElementById('setDescription');

        // Helper: check whether XML is well-formed
        function isWellFormedXml(xmlString) {
            const parser = new DOMParser();
            const parsed = parser.parseFromString(xmlString, 'application/xml');
            const parserError = parsed.getElementsByTagName('parsererror');
            return parserError.length === 0;
        }

        form.addEventListener('submit', function (e) {
            // First run standard HTML5 validation
            if (!this.checkValidity()) {
                e.preventDefault();
                const firstInvalid = this.querySelector(':invalid');
                if (firstInvalid) firstInvalid.focus();
                return; // skip further validation
            }

            // XML-specific validation
            const value = xmlInput.value.trim();
            if (value && !isWellFormedXml(value)) {
                e.preventDefault();
                xmlInput.classList.add('is-invalid');

                // Create feedback element only once
                let feedback = xmlInput.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.classList.add('invalid-feedback');
                    xmlInput.insertAdjacentElement('afterend', feedback);
                }

                feedback.textContent = 'The XML fragment appears malformed or incomplete.';
                xmlInput.focus();
            } else {
                xmlInput.classList.remove('is-invalid');
                const feedback = xmlInput.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.textContent = '';
                }
            }
        });
    });
</script>