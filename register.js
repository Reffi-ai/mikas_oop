// Mengambil elemen-elemen yang diperlukan
document.querySelector('form').addEventListener('submit', function (e) {
    const fullName = document.querySelector('input[name="full_name"]').value.trim();
    const warmindoName = document.querySelector('input[name="warmindo_name"]').value.trim();
    const email = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();

    // Validasi nama lengkap
    if (!fullName) {
        e.preventDefault();
        alert('Nama lengkap harus diisi!');
        return;
    }

    // Validasi nama warmindo
    if (!warmindoName) {
        e.preventDefault();
        alert('Nama warmindo harus diisi!');
        return;
    }

    // Validasi email
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        e.preventDefault();
        alert('Format email tidak valid!');
        return;
    }

    // Validasi panjang password
    if (password.length < 6) {
        e.preventDefault();
        alert('Password harus memiliki minimal 6 karakter!');
        return;
    }
});

// Mencegah zoom dengan Ctrl + Scroll
window.addEventListener('wheel', function(e) {
    if (e.ctrlKey) {
        e.preventDefault();
    }
}, { passive: false });

// Mencegah zoom dengan Ctrl + Plus/Minus
window.addEventListener('keydown', function(e) {
    if (e.ctrlKey && (e.key === '=' || e.key === '-' || e.key === '0')) {
        e.preventDefault();
    }
});

// Menampilkan atau menyembunyikan password
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', function () {
    // Toggle tipe input antara 'password' dan 'text'
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    // Ganti ikon
    this.src = type === 'password' ? 'icon/show.png' : 'icon/hide.png';
});