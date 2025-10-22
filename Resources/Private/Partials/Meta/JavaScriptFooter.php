<script>


    // Autofokus auf erstes ungültiges Feld bei fehlschlagendem Submit
    document.getElementById('metaForm').addEventListener('submit', function (e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            const firstInvalid = this.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
</script>