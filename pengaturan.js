// Fungsi untuk kembali ke halaman sebelumnya
const goBack = () => {
    window.location.href = 'dasboard.php';
};

// Aktifkan edit mode saat tombol Edit diklik
document.getElementById('editBtn').onclick = function() {
    document.getElementById('fullName').removeAttribute('readonly');
    document.getElementById('warmindoName').removeAttribute('readonly');
    document.getElementById('saveBtn').disabled = false;
    this.disabled = true; // tombol Edit jadi tidak bisa diklik lagi
};

const handleExitClick = (event) => {
    console.log('Tombol exit diklik');
    confirmExit(event);
};

const confirmExit = (event) => {
    return confirm('Apakah Anda yakin ingin keluar dari aplikasi?')
        ? redirectTo('index.html')
        : cancelLogout(event);
};

const redirectTo = (url) => {
    window.location.href = url;
};

const cancelLogout = (event) => {
    console.log('Logout dibatalkan');
    alert('Logout dibatalkan.');
    event.preventDefault();
};

const logout = () => redirectTo('logout.php');


const bindEvents = () => { // Mengikat event ke elemen-elemen
    const exitButton = document.querySelector('.btn.exit');
    exitButton?.addEventListener('click', handleExitClick);
};

// Inisialisasi semua event
const init = () => {
    bindEvents();
};

init();
