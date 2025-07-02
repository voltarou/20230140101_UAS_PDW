<?php
// views/asisten/dashboard.php

// Pastikan hanya asisten yang bisa mengakses halaman ini
if (!isAdmin()) {
    redirect('index.php?page=login');
}

$asisten_username = $_SESSION['username'] ?? 'Asisten';

// Ambil beberapa statistik atau data ringkasan untuk dashboard asisten
// Contoh: Jumlah mahasiswa terdaftar, jumlah praktikum, jumlah laporan belum dinilai
try {
    // Jumlah Mahasiswa Terdaftar
    $stmt_mahasiswa_count = $pdo->query("SELECT COUNT(*) FROM Users WHERE role = 'mahasiswa'");
    $total_mahasiswa = $stmt_mahasiswa_count->fetchColumn();

    // Jumlah Praktikum Aktif
    $stmt_praktikum_count = $pdo->query("SELECT COUNT(*) FROM MataPraktikum");
    $total_praktikum = $stmt_praktikum_count->fetchColumn();

    // Jumlah Laporan Belum Dinilai
    $stmt_ungraded_reports_count = $pdo->query("SELECT COUNT(*) FROM Laporan WHERE nilai IS NULL");
    $ungraded_reports = $stmt_ungraded_reports_count->fetchColumn();

    // Contoh: Beberapa laporan terbaru yang belum dinilai
    $stmt_latest_ungraded = $pdo->query("
        SELECT TOP 5 l.laporan_id, u.username, m.judul_modul, l.tanggal_kumpul
        FROM Laporan l
        JOIN Users u ON l.user_id = u.user_id
        JOIN Modul m ON l.modul_id = m.modul_id
        WHERE l.nilai IS NULL
        ORDER BY l.tanggal_kumpul DESC
    ");
    $latest_ungraded_reports = $stmt_latest_ungraded->fetchAll();

} catch (PDOException $e) {
    // Tangani error database
    echo "<div class='container'><p style='color: red;'>Error mengambil data dashboard: " . $e->getMessage() . "</p></div>";
    $total_mahasiswa = 0;
    $total_praktikum = 0;
    $ungraded_reports = 0;
    $latest_ungraded_reports = [];
}
?>

<div class="container dashboard-container">
    <h2 class="dashboard-welcome">Selamat Datang, <?php echo htmlspecialchars($asisten_username); ?>!</h2>
    <p class="dashboard-tagline">Ini adalah Ringkasan Dashboard Asisten Anda.</p>

    <div class="dashboard-stats-grid">
        <div class="stat-card">
            <h3>Total Mahasiswa</h3>
            <p class="stat-number"><?php echo $total_mahasiswa; ?></p>
            <a href="index.php?page=manage_users" class="stat-link">Lihat Detail</a>
        </div>
        <div class="stat-card">
            <h3>Mata Praktikum</h3>
            <p class="stat-number"><?php echo $total_praktikum; ?></p>
            <a href="index.php?page=manage_courses" class="stat-link">Kelola</a>
        </div>
        <div class="stat-card">
            <h3>Laporan Belum Dinilai</h3>
            <p class="stat-number"><?php echo $ungraded_reports; ?></p>
            <a href="index.php?page=incoming_reports&status=ungraded" class="stat-link">Nilai Sekarang</a>
        </div>
    </div>

    <div class="dashboard-section">
        <h3>5 Laporan Terbaru yang Belum Dinilai</h3>
        <?php if (empty($latest_ungraded_reports)): ?>
            <p>Tidak ada laporan yang belum dinilai saat ini. Kerja bagus!</p>
        <?php else: ?>
            <ul class="latest-reports-list">
                <?php foreach ($latest_ungraded_reports as $report): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($report['username']); ?></strong> -
                        Modul: <?php echo htmlspecialchars($report['judul_modul']); ?> (Dikumpul: <?php echo date('d M Y', strtotime($report['tanggal_kumpul'])); ?>)
                        <a href="index.php?page=grade_report&id=<?php echo $report['laporan_id']; ?>" class="button small grade-button">Nilai</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="dashboard-section quick-actions">
        <h3>Aksi Cepat</h3>
        <ul>
            <li><a href="index.php?page=manage_courses" class="button">Kelola Mata Praktikum</a></li>
            <li><a href="index.php?page=manage_modules" class="button">Kelola Modul Praktikum</a></li>
            <li><a href="index.php?page=incoming_reports" class="button">Lihat Semua Laporan</a></li>
            <li><a href="index.php?page=manage_users" class="button">Kelola Pengguna</a></li>
        </ul>
    </div>
</div>

<style>
    /* Styling khusus untuk dashboard asisten */
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
        color: #007bff;
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

    .grade-button {
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
        background-color: #6c757d;
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