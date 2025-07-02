// script.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('SIMPRAK JavaScript is loaded and running!');

    // --- 1. Validasi Formulir Sederhana (Contoh untuk Login Form) ---
    // Anda bisa mengadaptasi ini untuk formulir lain
    const loginForm = document.querySelector('form[action="index.php?page=login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const usernameInput = loginForm.querySelector('#username');
            const passwordInput = loginForm.querySelector('#password');

            if (!usernameInput.value.trim()) {
                alertMessage('Username tidak boleh kosong!', 'error');
                event.preventDefault(); // Mencegah form disubmit
                return false;
            }
            if (!passwordInput.value.trim()) {
                alertMessage('Password tidak boleh kosong!', 'error');
                event.preventDefault(); // Mencegah form disubmit
                return false;
            }
            // Jika validasi lolos, form akan disubmit secara normal
            return true;
        });
    }

    // --- 2. Konfirmasi Hapus Kustom (Menggantikan window.confirm()) ---
    // Cari semua link dengan kelas 'delete-action'
    const deleteLinks = document.querySelectorAll('a[href*="action=delete"]');

    deleteLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah navigasi langsung

            const deleteUrl = this.href;
            const message = this.dataset.confirmMessage || 'Anda yakin ingin menghapus data ini?';

            // Tampilkan modal konfirmasi kustom
            showConfirmModal(message, function() {
                // Jika pengguna mengkonfirmasi, arahkan ke URL hapus
                window.location.href = deleteUrl;
            });
        });
    });

    // --- Fungsi untuk menampilkan pesan alert kustom (menggantikan window.alert()) ---
    function alertMessage(message, type = 'info') {
        const alertBox = document.createElement('div');
        alertBox.classList.add('custom-alert', type); // Tambahkan kelas untuk styling (misal: .custom-alert.error)
        alertBox.textContent = message;

        // Styling dasar untuk alert (Anda bisa pindahkan ke CSS)
        alertBox.style.padding = '10px';
        alertBox.style.margin = '10px 0';
        alertBox.style.borderRadius = '5px';
        alertBox.style.textAlign = 'center';
        alertBox.style.fontWeight = 'bold';
        alertBox.style.color = 'white';

        if (type === 'error') {
            alertBox.style.backgroundColor = '#dc3545'; // Merah untuk error
        } else if (type === 'success') {
            alertBox.style.backgroundColor = '#28a745'; // Hijau untuk sukses
        } else {
            alertBox.style.backgroundColor = '#007bff'; // Biru untuk info
        }

        document.body.prepend(alertBox); // Tambahkan di bagian atas body

        // Hilangkan alert setelah beberapa detik
        setTimeout(() => {
            alertBox.remove();
        }, 3000);
    }

    // --- Fungsi untuk menampilkan modal konfirmasi kustom ---
    function showConfirmModal(message, onConfirmCallback) {
        // Buat elemen modal
        const modalOverlay = document.createElement('div');
        modalOverlay.classList.add('modal-overlay');
        modalOverlay.style.position = 'fixed';
        modalOverlay.style.top = '0';
        modalOverlay.style.left = '0';
        modalOverlay.style.width = '100%';
        modalOverlay.style.height = '100%';
        modalOverlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        modalOverlay.style.display = 'flex';
        modalOverlay.style.justifyContent = 'center';
        modalOverlay.style.alignItems = 'center';
        modalOverlay.style.zIndex = '1000';

        const modalContent = document.createElement('div');
        modalContent.classList.add('modal-content');
        modalContent.style.backgroundColor = 'white';
        modalContent.style.padding = '20px';
        modalContent.style.borderRadius = '8px';
        modalContent.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        modalContent.style.textAlign = 'center';
        modalContent.style.maxWidth = '400px';
        modalContent.style.width = '90%';

        const modalMessage = document.createElement('p');
        modalMessage.textContent = message;
        modalMessage.style.marginBottom = '20px';
        modalMessage.style.fontSize = '1.1em';

        const confirmButton = document.createElement('button');
        confirmButton.textContent = 'Ya';
        confirmButton.style.backgroundColor = '#dc3545';
        confirmButton.style.color = 'white';
        confirmButton.style.padding = '8px 15px';
        confirmButton.style.border = 'none';
        confirmButton.style.borderRadius = '5px';
        confirmButton.style.cursor = 'pointer';
        confirmButton.style.marginRight = '10px';

        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'Tidak';
        cancelButton.style.backgroundColor = '#6c757d';
        cancelButton.style.color = 'white';
        cancelButton.style.padding = '8px 15px';
        cancelButton.style.border = 'none';
        cancelButton.style.borderRadius = '5px';
        cancelButton.style.cursor = 'pointer';

        // Tambahkan event listener
        confirmButton.addEventListener('click', function() {
            onConfirmCallback();
            modalOverlay.remove(); // Hapus modal setelah konfirmasi
        });

        cancelButton.addEventListener('click', function() {
            modalOverlay.remove(); // Hapus modal jika dibatalkan
        });

        // Bangun struktur modal
        modalContent.appendChild(modalMessage);
        modalContent.appendChild(confirmButton);
        modalContent.appendChild(cancelButton);
        modalOverlay.appendChild(modalContent);

        document.body.appendChild(modalOverlay); // Tambahkan modal ke body
    }

    // --- 3. Efek UI Dasar (Contoh: Menambahkan kelas 'active' ke navigasi) ---
    // Ini membutuhkan elemen navigasi di HTML Anda, misalnya:
    // <nav>
    //   <a href="index.php?page=home" data-page="home">Home</a>
    //   <a href="index.php?page=my_practicums" data-page="my_practicums">Praktikum Saya</a>
    // </nav>
    const navLinks = document.querySelectorAll('nav a');
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'home';

    navLinks.forEach(link => {
        if (link.dataset.page === currentPage) {
            link.classList.add('active-nav-link'); // Tambahkan kelas 'active-nav-link'
        }
    });

    // Anda perlu menambahkan styling untuk .active-nav-link di CSS Anda
    // Contoh di CSS:
    // .active-nav-link {
    //     font-weight: bold;
    //     color: #0056b3;
    //     border-bottom: 2px solid #0056b3;
    // }

});
