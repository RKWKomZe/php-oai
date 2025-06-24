</body>


<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div id="feedbackToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="feedbackToastBody">
                Erfolgreich importiert.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="modal fade" id="importConfirmModal" tabindex="-1" aria-labelledby="importConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importConfirmLabel">Import bestätigen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Abbrechen"></button>
            </div>
            <div class="modal-body">
                Möchten Sie dieses Produkt wirklich importieren?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <button type="button" class="btn btn-primary" id="confirmImportBtn">Ja, importieren</button>
            </div>
        </div>
    </div>
</div>

</html>
