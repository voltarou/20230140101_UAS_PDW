<?php
// includes/header.php

// Pastikan session sudah dimulai di index.php atau di sini jika ini satu-satunya file yang di-include pertama
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

// Dapatkan peran pengguna yang sedang login
$user_role = $_SESSION['role'] ?? 'guest';
$username = $_SESSION['username'] ?? 'Tamu';

// Tentukan halaman-halaman yang TIDAK akan menampilkan navigasi penuh (judul SIMPRAK + menu navigasi utama)
// Ini termasuk halaman landing, login, register, semua halaman manajemen asisten, dan halaman mahasiswa
$pages_without_full_nav = [
    'home', // Halaman landing awal
    'login',
    'register',
    // Halaman Asisten
    'manage_courses',
    'manage_modules',
    'incoming_reports',
    'grade_report',
    'manage_users',
    // Halaman Mahasiswa
    'public_courses',   // Daftar praktikum publik
    'enroll_course',    // Proses pendaftaran praktikum
    'my_practicums',    // Praktikum yang diikuti mahasiswa
    'practicum_detail', // Detail praktikum mahasiswa
    // Tambahkan halaman form atau detail lainnya di sini jika ada
];

$current_page = $_GET['page'] ?? 'home'; // Default ke 'home' jika parameter 'page' tidak diset

// Variabel ini akan menentukan apakah navigasi penuh harus ditampilkan
$show_full_header_nav = !in_array($current_page, $pages_without_full_nav);

// Tentukan apakah container header itu sendiri harus ditampilkan.
// Header harus ditampilkan jika:
// 1. Navigasi penuh diperlukan (misal: dashboard untuk user login, public_courses untuk user non-login).
// 2. Pengguna sudah login (karena kita perlu menampilkan tombol logout).
$show_header_container = $show_full_header_nav || isLoggedIn();

// Fungsi isLoggedIn() dan isAdmin() diasumsikan sudah tersedia dari includes/function.php
// Pastikan includes/function.php sudah di-require_once di public/index.php sebelum header ini di-include.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMPRAK - Sistem Informasi Manajemen Praktikum</title>
    <!-- Link ke file CSS utama Anda -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php if ($show_header_container): // Tampilkan container header hanya jika ada konten di dalamnya ?>
        <header class="app-header">
            <div class="header-content">
                <div class="header-left">
                    <?php if ($show_full_header_nav): // Tampilkan judul SIMPRAK hanya jika navigasi penuh aktif ?>
                        <h1 class="site-title"><a href="index.php">SIMPRAK</a></h1>
                    <?php endif; ?>
                </div>

                <div class="header-right">
                    <?php if ($show_full_header_nav): // Tampilkan navigasi utama hanya jika navigasi penuh aktif ?>
                        <nav class="main-nav">
                            <ul>
                                <?php if (isLoggedIn()): ?>
                                    <li><a href="index.php?page=home" <?php echo ($current_page == 'home') ? 'class="active"' : ''; ?>>Dashboard</a></li>
                                    <?php if ($user_role === 'mahasiswa'): ?>
                                        <li><a href="index.php?page=public_courses" <?php echo ($current_page == 'public_courses') ? 'class="active"' : ''; ?>>Cari Praktikum</a></li>
                                        <li><a href="index.php?page=my_practicums" <?php echo ($current_page == 'my_practicums') ? 'class="active"' : ''; ?>>Praktikum Saya</a></li>
                                    <?php elseif ($user_role === 'asisten'): ?>
                                        <li><a href="index.php?page=manage_courses" <?php echo ($current_page == 'manage_courses') ? 'class="active"' : ''; ?>>Kelola Mata Praktikum</a></li>
                                        <li><a href="index.php?page=manage_modules" <?php echo ($current_page == 'manage_modules') ? 'class="active"' : ''; ?>>Kelola Modul</a></li>
                                        <li><a href="index.php?page=incoming_reports" <?php echo ($current_page == 'incoming_reports') ? 'class="active"' : ''; ?>>Laporan Masuk</a></li>
                                        <li><a href="index.php?page=manage_users" <?php echo ($current_page == 'manage_users') ? 'class="active"' : ''; ?>>Kelola Pengguna</a></li>
                                    <?php endif; ?>
                                <?php else: // Jika belum login dan navigasi penuh aktif (misal: di public_courses) ?>
                                    <li><a href="index.php?page=login" <?php echo ($current_page == 'login') ? 'class="active"' : ''; ?>>Login</a></li>
                                    <li><a href="index.php?page=register" <?php echo ($current_page == 'register') ? 'class="active"' : ''; ?>>Daftar</a></li>
                                    <li><a href="index.php?page=public_courses" <?php echo ($current_page == 'public_courses') ? 'class="active"' : ''; ?>>Daftar Praktikum</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                    <?php if (isLoggedIn()): // Selalu tampilkan info pengguna dan tombol logout jika sudah login ?>
                        <div class="user-info-standalone">
                            <span>Halo, <?php echo htmlspecialchars($username); ?>!</span>
                            <a href="index.php?page=logout" class="logout-button">Logout</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>
    <?php endif; // Akhir dari kondisi show_header_container ?>
    <main class="app-main-content">
        <!-- Konten spesifik halaman akan dimuat di sini -->
