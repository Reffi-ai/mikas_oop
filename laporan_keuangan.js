// Menyembunyikan notifikasi setelah 3 detik (Functional Style)
const hideNotifications = () => ['success-alert', 'error-alert'].forEach(hideElement);

setTimeout(hideNotifications, 3000);

const hideElement = (id) => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
};  

// Fungsi toggle semua checkbox (Functional Style)
const toggleCheckboxes = (masterCheckbox) => 
    Array.from(document.querySelectorAll('input[name="ids[]"]'))
        .forEach(checkbox => checkbox.checked = masterCheckbox.checked);
