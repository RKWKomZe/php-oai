
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

