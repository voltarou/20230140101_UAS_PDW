<?php
// public/index.php

// Memuat file konfigurasi (koneksi database) dan fungsi-fungsi umum
require_once __DIR__ . '/../includes/config.php'; // Menggunakan config.php
require_once __DIR__ . '/../includes/function.php';

// Simple routing mechanism
$page = $_GET['page'] ?? 'home'; // Default to 'home'

// Basic authentication check for protected pages
// Halaman 'login', 'register', 'home', dan 'public_courses' dapat diakses tanpa login
if (!isLoggedIn() && !in_array($page, ['login', 'register', 'home', 'public_courses'])) {
    redirect('index.php?page=login');
}

// Sertakan header HTML yang umum
include __DIR__ . '/../includes/header.php'; // Uncommented

// Route to the appropriate page based on $page parameter
switch ($page) {
    case 'home':
        // Display a general welcome page or redirect based on user role
        if (isLoggedIn()) {
            if (isAdmin()) {
                // Jika asisten, arahkan ke dashboard asisten
                include __DIR__ . '/../views/asisten/dashboard.php';
            } else {
                // Jika mahasiswa, arahkan ke dashboard mahasiswa
                include __DIR__ . '/../views/mahasiswa/dashboard.php';
            }
        } else {
            // Jika belum login, tampilkan pesan selamat datang dengan link login/daftar
            echo "<h1>Selamat Datang di SIMPRAK</h1>";
            echo "<p>Sistem Informasi Manajemen Praktikum.</p>";
            echo "<p>Silakan <a href='index.php?page=login'>Login</a> atau <a href='index.php?page=register'>Daftar</a>.</p>";
        }
        break;

    case 'login':
        // Halaman login
        include __DIR__ . '/../views/auth/login.php';
        break;

    case 'register':
        // Halaman registrasi
        include __DIR__ . '/../views/auth/register.php';
        break;

    case 'logout':
        // Proses logout: hancurkan session dan redirect ke halaman login
        session_destroy();
        redirect('index.php?page=login');
        break;

    // --- Fungsionalitas Mahasiswa ---
    case 'public_courses':
        // Halaman katalog mata praktikum yang bisa diakses publik
        include __DIR__ . '/../views/mahasiswa/praktikum_list.php';
        break;

    case 'enroll_course':
        // Logika untuk mendaftar ke praktikum (membutuhkan login sebagai mahasiswa)
        if (!isLoggedIn() || isAdmin()) {
            redirect('index.php?page=login');
        }
        // Ini adalah tempat Anda akan menulis logika pendaftaran ke praktikum
        // atau menyertakan file terpisah yang menangani proses ini.
        // Contoh sederhana:
        $praktikum_id_to_enroll = $_GET['id'] ?? null;
        if ($praktikum_id_to_enroll) {
            $user_id = $_SESSION['user_id'];
            try {
                // Cek apakah sudah terdaftar
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM PendaftaranPraktikum WHERE user_id = ? AND praktikum_id = ?");
                $stmt_check->execute([$user_id, $praktikum_id_to_enroll]);
                if ($stmt_check->fetchColumn() > 0) {
                    echo "<div class='container'><p style='color: orange;'>Anda sudah terdaftar di praktikum ini.</p></div>";
                } else {
                    $stmt_enroll = $pdo->prepare("INSERT INTO PendaftaranPraktikum (user_id, praktikum_id, tanggal_daftar) VALUES (?, ?, GETDATE())");
                    if ($stmt_enroll->execute([$user_id, $praktikum_id_to_enroll])) {
                        echo "<div class='container'><p style='color: green;'>Berhasil mendaftar ke praktikum!</p></div>";
                    } else {
                        echo "<div class='container'><p style='color: red;'>Gagal mendaftar ke praktikum.</p></div>";
                    }
                }
            } catch (PDOException $e) {
                echo "<div class='container'><p style='color: red;'>Error pendaftaran: " . $e->getMessage() . "</p></div>";
            }
        } else {
            echo "<div class='container'><p style='color: red;'>ID Praktikum tidak ditemukan untuk pendaftaran.</p></div>";
        }
        echo "<div class='container'><p><a href='index.php?page=my_practicums'>Kembali ke Praktikum Saya</a></p></div>";
        break;

    case 'my_practicums':
        // Halaman daftar praktikum yang diikuti oleh mahasiswa yang sedang login
        if (!isLoggedIn() || isAdmin()) {
            redirect('index.php?page=login');
        }
        include __DIR__ . '/../views/mahasiswa/my_practicums.php';
        break;

    case 'practicum_detail':
        // Halaman detail praktikum, tugas, pengumpulan laporan, dan nilai
        if (!isLoggedIn() || isAdmin()) {
            redirect('index.php?page=login');
        }
        include __DIR__ . '/../views/mahasiswa/praktikum_detail.php';
        break;

    // --- Fungsionalitas Asisten ---
    case 'manage_courses':
        // Halaman CRUD untuk mengelola mata praktikum
        if (!isAdmin()) {
            redirect('index.php?page=login');
        }
        include __DIR__ . '/../views/asisten/manage_course.php';
        break;

    case 'manage_modules':
        // Halaman CRUD untuk mengelola modul/pertemuan
        if (!isAdmin()) {
            redirect('index.php?page=login');
        }
        include __DIR__ . '/../views/asisten/manage_modules.php';
        break;

    case 'incoming_reports':
        // Halaman untuk melihat semua laporan yang masuk (dengan filter)
        if (!isAdmin()) {
            redirect('index.php?page=login');
        }
        include __DIR__ . '/../views/asisten/incoming_reports.php';
        break;

    case 'grade_report':
        // Halaman untuk memberi nilai dan feedback pada laporan
        if (!isAdmin()) {
            redirect('index.php?page=login');
        }
        include __DIR__ . '/../views/asisten/grade_report.php';
        break;

    case 'manage_users':
        // Halaman CRUD untuk mengelola akun pengguna (mahasiswa dan asisten lainnya)
        if (!isAdmin()) {
            redirect('index.php?page=login');
        }
        include __DIR__ . '/../views/asisten/manage_users.php';
        break;

    default:
        // Halaman 404 jika rute tidak ditemukan
        include __DIR__ . '/../views/404.php';
        break;
}

// Sertakan footer HTML yang umum
include __DIR__ . '/../includes/footer.php'; // Uncommented
