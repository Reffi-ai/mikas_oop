// Fungsi murni untuk mencegah zoom saat scroll (ctrl + scroll)
const preventZoomOnWheel = (e) => 
    e.ctrlKey && e.preventDefault();

// Fungsi murni untuk mencegah zoom dengan tombol plus/minus/0
const preventZoomOnKeydown = (e) => 
    e.ctrlKey && ['=', '-', '0'].includes(e.key) && e.preventDefault();

// Menambahkan event listener untuk mencegah zoom saat scroll dan tombol keyboard
window.addEventListener('wheel', preventZoomOnWheel, { passive: false });
window.addEventListener('keydown', preventZoomOnKeydown);