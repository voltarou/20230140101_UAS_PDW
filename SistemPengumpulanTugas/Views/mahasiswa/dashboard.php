<?php
// views/mahasiswa/dashboard.php

// Pastikan hanya mahasiswa yang bisa mengakses halaman ini
// Fungsi isLoggedIn() dan isAdmin() diasumsikan ada di includes/functions.php
// $pdo (koneksi database) juga diasumsikan tersedia dari index.php yang memuat config.php
if (!isLoggedIn() || isAdmin()) {
    redirect('index.php?page=login');
}

// Mengambil username dari session
$mahasiswa_username = $_SESSION['username'] ?? 'Mahasiswa';
$user_id = $_SESSION['user_id'] ?? null; // Pastikan user_id tersedia di session setelah login

// Inisialisasi variabel statistik
$total_praktikum_diikuti = 0;
$total_tugas_selesai = 0;
$total_tugas_menunggu = 0;

// Lakukan query database hanya jika user_id tersedia
if ($user_id) {
    try {
        // 1. Jumlah Praktikum Diikuti oleh mahasiswa ini
        $stmt_praktikum = $pdo->prepare("SELECT COUNT(DISTINCT praktikum_id) FROM PendaftaranPraktikum WHERE user_id = ?");
        $stmt_praktikum->execute([$user_id]);
        $total_praktikum_diikuti = $stmt_praktikum->fetchColumn();

        // 2. Jumlah Tugas Selesai (Laporan yang sudah dinilai)
        // Diasumsikan 'nilai' NULL berarti belum dinilai, dan TIDAK NULL berarti sudah dinilai
        $stmt_tugas_selesai = $pdo->prepare("SELECT COUNT(*) FROM Laporan WHERE user_id = ? AND nilai IS NOT NULL");
        $stmt_tugas_selesai->execute([$user_id]);
        $total_tugas_selesai = $stmt_tugas_selesai->fetchColumn();

        // 3. Jumlah Tugas Menunggu (Laporan yang belum dinilai)
        // Kita hitung laporan yang sudah dikumpul tapi nilai-nya masih NULL
        $stmt_tugas_menunggu = $pdo->prepare("SELECT COUNT(*) FROM Laporan WHERE user_id = ? AND nilai IS NULL");
        $stmt_tugas_menunggu->execute([$user_id]);
        $total_tugas_menunggu = $stmt_tugas_menunggu->fetchColumn();

    } catch (PDOException $e) {
        // Tangani error database jika terjadi masalah saat mengambil data
        echo "<div class='container'><p style='color: red;'>Error mengambil data dashboard: " . $e->getMessage() . "</p></div>";
        // Reset statistik agar tidak menampilkan data salah jika ada error
        $total_praktikum_diikuti = 0;
        $total_tugas_selesai = 0;
        $total_tugas_menunggu = 0;
    }
}
?>

<div class="container dashboard-container">
    <h2 class="dashboard-welcome">Selamat Datang Kembali, <?php echo htmlspecialchars($mahasiswa_username); ?>!</h2>
    <p class="dashboard-tagline">Ini adalah Ringkasan Dashboard Praktikum Anda.</p>

    <div class="dashboard-stats-grid">
        <div class="stat-card">
            <h3>Praktikum Diikuti</h3>
            <p class="stat-number"><?php echo $total_praktikum_diikuti; ?></p>
            <a href="index.php?page=my_practicums" class="stat-link">Lihat Praktikum Saya</a>
        </div>
        
        <div class="stat-card">
            <h3>Tugas Selesai</h3>
            <p class="stat-number"><?php echo $total_tugas_selesai; ?></p>
            <a href="index.php?page=my_practicums" class="stat-link">Cek Nilai</a>
        </div>
        
        <div class="stat-card">
            <h3>Tugas Menunggu</h3>
            <p class="stat-number"><?php echo $total_tugas_menunggu; ?></p>
            <a href="index.php?page=my_practicums" class="stat-link">Unggah Laporan</a>
        </div>
    </div>

    <div class="dashboard-section">
        <h3>Notifikasi Terbaru</h3>
        <ul class="latest-reports-list">
            <p>Tidak ada notifikasi baru saat ini.</p>
        </ul>
    </div>

    <div class="dashboard-section quick-actions">
        <h3>Aksi Cepat</h3>
        <ul>
            <li><a href="index.php?page=public_courses" class="button">Cari Praktikum Baru</a></li>
            <li><a href="index.php?page=my_practicums" class="button">Lihat Praktikum Saya</a></li>
            <li><a href="index.php?page=my_practicums" class="button">Unggah Laporan</a></li>
        </ul>
    </div>
</div>

<style>
    /* Styling umum untuk dashboard (disalin dari dashboard asisten agar konsisten) */
    .dashboard-container {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .dashboard-welcome {
        color: #0056b3;
        margin-bottom: 10px;
    }

    .dashboard-tagline {
        color: #555;
        margin-bottom: 30px;
    }

    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s ease-in-out;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card h3 {
        color: #333;
        font-size: 1.2em;
        margin-bottom: 10px;
    }

    .stat-card .stat-number {
        font-size: 2.5em;
        font-weight: bold;
        color: #007bff; /* Warna biru untuk angka statistik */
        margin-bottom: 15px;
    }

    .stat-card .stat-link {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.2s ease;
    }

    .stat-card .stat-link:hover {
        background-color: #0056b3;
    }

    .dashboard-section {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .dashboard-section h3 {
        color: #333;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .latest-reports-list {
        list-style: none;
        padding: 0;
    }

    .latest-reports-list li {
        padding: 10px 0;
        border-bottom: 1px dashed #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .latest-reports-list li:last-child {
        border-bottom: none;
    }

    /* Styling untuk tombol di notifikasi (jika ada) */
    .grade-button { /* Nama kelas ini mungkin tidak relevan untuk mahasiswa, tapi tetap ada di CSS asisten */
        background-color: #28a745;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 0.9em;
        transition: background-color 0.2s ease;
    }
    .grade-button:hover {
        background-color: #218838;
    }

    .quick-actions ul {
        list-style: none;
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .quick-actions li .button {
        background-color: #6c757d; /* Warna abu-abu untuk tombol aksi cepat */
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: background-color 0.2s ease;
    }

    .quick-actions li .button:hover {
        background-color: #5a6268;
    }
</style>