// Fungsi utilitas
const isEmpty = (value) => value.trim() === ''; // Cek apakah string kosong
const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); // Cek format email
const isValidPasswordLength = (password, minLength = 6) => password.length >= minLength; // Cek panjang password

// Fungsi validasi form login
const validateForm = (email, password) => {
    if (isEmpty(email) || isEmpty(password)) {
        return 'Email dan password harus diisi!';
    }
    if (!isValidEmail(email)) {
        return 'Format email tidak valid!';
    }
    if (!isValidPasswordLength(password)) {
        return 'Password harus memiliki minimal 6 karakter!';
    }
    return null;
};

// Fungsi untuk menangani submit form
const handleFormSubmit = (e) => {
    const email = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();
    const errorMessage = validateForm(email, password);

    if (errorMessage) {
        e.preventDefault();
        alert(errorMessage);
    }
};

// Cegah zoom dengan Ctrl + Scroll
const preventZoomScroll = (e) => {
    if (e.ctrlKey) {
        e.preventDefault();
    }
};

// Cegah zoom dengan Ctrl + + / - / 0
const preventZoomKey = (e) => {
    if (e.ctrlKey && ['=', '-', '0'].includes(e.key)) {
        e.preventDefault();
    }
};

// Toggle tampilan password
const togglePasswordVisibility = () => {
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    const toggleType = (input) => input.getAttribute('type') === 'password' ? 'text' : 'password';
    const toggleIcon = (type) => type === 'password' ? 'icon/show.png' : 'icon/hide.png';

    const newType = toggleType(passwordInput);
    passwordInput.setAttribute('type', newType);
    togglePassword.src = toggleIcon(newType);
};

// Pasang semua event listener
const setupEventListeners = () => {
    document.querySelector('form').addEventListener('submit', handleFormSubmit);
    window.addEventListener('wheel', preventZoomScroll, { passive: false });
    window.addEventListener('keydown', preventZoomKey);
    document.getElementById('togglePassword').addEventListener('click', togglePasswordVisibility);
};

// Inisialisasi
setupEventListeners();
