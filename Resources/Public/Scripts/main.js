
// Auto-dismiss alerts after 5 seconds
/*
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert-dismissible');

    alerts.forEach(function (alert) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000); // Zeit in Millisekunden
    });
});
 */

document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert-dismissible');

    alerts.forEach(function (alert) {
        const timeout = 15000;

        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, timeout);
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.import-button').forEach(button => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();

            if (!confirm('Are you sure you want to import this record?')) {
                return;
            }

            const url = button.dataset.importUrl;
            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (parseError) {
                    showError(`Ungültige JSON-Antwort:\n\n${text}`);
                    return;
                }

                if (response.ok && result.success) {
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-success');
                    button.textContent = 'Importiert';
                    button.disabled = true;

                    // ✅ Overlay-Häkchen dynamisch einfügen
                    const card = button.closest('[data-product-id-container]');
                    if (card && !card.querySelector('.imported-overlay')) {
                        const overlay = document.createElement('div');
                        overlay.className = 'imported-overlay';
                        overlay.innerHTML = '<i class="bi bi-check-circle-fill check-icon" title="Bereits importiert"></i>';
                        card.appendChild(overlay, card.firstChild);
                    }

                    // Optional: kleines Infofeld oder Toast
                    //showSuccess(`Produkt ${button.dataset.productId} erfolgreich importiert.`);
                } else {
                    showError(`Fehler beim Import: ${result.message || 'Unbekannter Fehler'}`);
                }

            } catch (err) {
                showError(`Fehler beim Import: ${err.message}`);
            }
        });
    });
});

function showSuccess(message) {
    alert(message); // oder schöner mit Modal
}

function showError(message) {
    alert(message); // für jetzt okay, später ersetzbar
}