<?php
// views/mahasiswa/praktikum_list.php

// $pdo (koneksi database) diasumsikan tersedia dari index.php yang memuat config.php
// isLoggedIn() dan isAdmin() diasumsikan tersedia dari includes/functions.php

// Query semua mata praktikum dari database
try {
    $stmt = $pdo->query("SELECT praktikum_id, nama_praktikum, deskripsi FROM MataPraktikum ORDER BY nama_praktikum ASC");
    $praktikums = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div class='container'><p style='color: red;'>Error mengambil data praktikum: " . $e->getMessage() . "</p></div>";
    $praktikums = []; // Pastikan $praktikums tetap array kosong jika ada error
}

// Cek apakah pengguna sudah login sebagai mahasiswa
$is_mahasiswa_logged_in = isLoggedIn() && !isAdmin();
$current_user_id = $_SESSION['user_id'] ?? null;

// Jika mahasiswa logged in, ambil daftar praktikum yang sudah didaftarinya
$enrolled_praktikums = [];
if ($is_mahasiswa_logged_in && $current_user_id) {
    try {
        $stmt_enrolled = $pdo->prepare("SELECT praktikum_id FROM PendaftaranPraktikum WHERE user_id = ?");
        $stmt_enrolled->execute([$current_user_id]);
        $enrolled_praktikums_raw = $stmt_enrolled->fetchAll(PDO::FETCH_COLUMN);
        // Konversi ke associative array atau set untuk pencarian cepat
        $enrolled_praktikums = array_flip($enrolled_praktikums_raw);
    } catch (PDOException $e) {
        echo "<div class='container'><p style='color: red;'>Error mengambil data pendaftaran: " . $e->getMessage() . "</p></div>";
    }
}
?>

<div class="container praktikum-list-container">
    <h2 class="section-title">Daftar Mata Praktikum yang Tersedia</h2>
    <p class="section-description">Jelajahi semua mata praktikum yang dapat Anda ikuti. Daftarkan diri Anda untuk memulai!</p>

    <?php if (empty($praktikums)): ?>
        <p class="no-data-message">Belum ada mata praktikum yang tersedia saat ini.</p>
    <?php else: ?>
        <div class="praktikum-grid">
            <?php foreach ($praktikums as $praktikum): ?>
                <div class="praktikum-card">
                    <h3 class="praktikum-title"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h3>
                    <p class="praktikum-description"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>
                    
                    <div class="praktikum-actions">
                        <?php if ($is_mahasiswa_logged_in): ?>
                            <?php if (isset($enrolled_praktikums[$praktikum['praktikum_id']])): ?>
                                <span class="status-badge enrolled">Sudah Terdaftar</span>
                                <a href="index.php?page=practicum_detail&id=<?php echo $praktikum['praktikum_id']; ?>" class="button view-detail-button">Lihat Praktikum</a>
                            <?php else: ?>
                                <a href="index.php?page=enroll_course&id=<?php echo $praktikum['praktikum_id']; ?>" class="button enroll-button">Daftar Sekarang</a>
                            <?php endif; ?>
                        <?php elseif (!isLoggedIn()): ?>
                            <p class="login-prompt">
                                <a href="index.php?page=login" class="button login-prompt-button">Login untuk Mendaftar</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Styling khusus untuk praktikum_list.php */
    .praktikum-list-container {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
    }

    .section-title {
        color: #0056b3;
        margin-bottom: 10px;
    }

    .section-description {
        color: #555;
        margin-bottom: 30px;
    }

    .no-data-message {
        text-align: center;
        color: #777;
        font-style: italic;
        padding: 30px;
        border: 1px dashed #ccc;
        border-radius: 8px;
    }

    .praktikum-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .praktikum-card {
        background-color: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* Untuk menempatkan tombol di bawah */
        transition: transform 0.2s ease-in-out;
    }

    .praktikum-card:hover {
        transform: translateY(-5px);
    }

    .praktikum-title {
        color: #007bff;
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 1.5em;
    }

    .praktikum-description {
        font-size: 0.95em;
        line-height: 1.5;
        color: #666;
        flex-grow: 1; /* Agar deskripsi mengisi ruang yang tersedia */
        margin-bottom: 20px;
    }

    .praktikum-actions {
        margin-top: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-start; /* Agar tombol tidak melebar penuh */
    }

    .button {
        display: inline-block;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        text-align: center;
        transition: background-color 0.2s ease, transform 0.1s ease;
    }

    .enroll-button {
        background-color: #28a745; /* Hijau untuk daftar */
        color: white;
    }

    .enroll-button:hover {
        background-color: #218838;
        transform: translateY(-1px);
    }

    .view-detail-button {
        background-color: #007bff; /* Biru untuk lihat detail */
        color: white;
    }

    .view-detail-button:hover {
        background-color: #0056b3;
        transform: translateY(-1px);
    }

    .login-prompt {
        font-size: 0.9em;
        color: #777;
        text-align: center;
        width: 100%;
    }

    .login-prompt-button {
        background-color: #ffc107; /* Kuning untuk login prompt */
        color: #333;
    }

    .login-prompt-button:hover {
        background-color: #e0a800;
        transform: translateY(-1px);
    }

    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.8em;
        font-weight: bold;
        color: white;
        margin-bottom: 10px;
    }

    .status-badge.enrolled {
        background-color: #17a2b8; /* Info blue */
    }
</style>
