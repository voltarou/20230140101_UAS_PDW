<?php
if (!isAdmin()) {
    redirect('index.php?page=login');
}

$laporan_id = $_GET['id'] ?? null;
$message = '';

if (!$laporan_id) {
    redirect('index.php?page=incoming_reports');
}

// Ambil detail laporan
$stmt_report = $pdo->prepare("
    SELECT
        l.laporan_id,
        l.file_laporan,
        l.tanggal_kumpul,
        l.nilai,
        l.feedback,
        u.username AS mahasiswa_username,
        u.email AS mahasiswa_email,
        m.judul_modul,
        mp.nama_praktikum
    FROM Laporan l
    JOIN Users u ON l.user_id = u.user_id
    JOIN Modul m ON l.modul_id = m.modul_id
    JOIN MataPraktikum mp ON m.praktikum_id = mp.praktikum_id
    WHERE l.laporan_id = ?
");
$stmt_report->execute([$laporan_id]);
$report_detail = $stmt_report->fetch();

if (!$report_detail) {
    echo "<div class='container'><p style='color: red;'>Laporan tidak ditemukan.</p></div>";
    exit();
}

// Handle submit nilai dan feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grade'])) {
    $nilai = $_POST['nilai'] ?? null;
    $feedback = $_POST['feedback'] ?? '';

    // Validasi nilai
    if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $message = "<p style='color: red;'>Nilai harus berupa angka antara 0 dan 100.</p>";
    } else {
        $stmt_update = $pdo->prepare("UPDATE Laporan SET nilai = ?, feedback = ? WHERE laporan_id = ?");
        if ($stmt_update->execute([$nilai, $feedback, $laporan_id])) {
            $message = "<p style='color: green;'>Nilai dan feedback berhasil disimpan!</p>";
            // Perbarui detail laporan setelah update
            $stmt_report->execute([$laporan_id]);
            $report_detail = $stmt_report->fetch();
        } else {
            $message = "<p style='color: red;'>Gagal menyimpan nilai dan feedback.</p>";
        }
    }
}
?>

<div class="container">
    <h2>Beri Nilai Laporan</h2>
    <p>Detail Laporan untuk Penilaian:</p>

    <?php echo $message; ?>

    <div class="report-details-card">
        <p><strong>Mahasiswa:</strong> <?php echo htmlspecialchars($report_detail['mahasiswa_username']); ?> (<?php echo htmlspecialchars($report_detail['mahasiswa_email']); ?>)</p>
        <p><strong>Praktikum:</strong> <?php echo htmlspecialchars($report_detail['nama_praktikum']); ?></p>
        <p><strong>Modul:</strong> <?php echo htmlspecialchars($report_detail['judul_modul']); ?></p>
        <p><strong>Tanggal Kumpul:</strong> <?php echo date('d M Y H:i', strtotime($report_detail['tanggal_kumpul'])); ?></p>
        <p><strong>File Laporan:</strong> <a href="<?php echo htmlspecialchars($report_detail['file_laporan']); ?>" target="_blank" class="download-link">Unduh Laporan</a></p>

        <h3 style="margin-top: 25px;">Form Penilaian</h3>
        <form action="index.php?page=grade_report&id=<?php echo $laporan_id; ?>" method="POST">
            <label for="nilai">Nilai (0-100):</label>
            <input type="number" id="nilai" name="nilai" min="0" max="100" step="1" value="<?php echo htmlspecialchars($report_detail['nilai'] ?? ''); ?>" required>
            <br>
            <label for="feedback">Feedback:</label>
            <textarea id="feedback" name="feedback" rows="5"><?php echo htmlspecialchars($report_detail['feedback'] ?? ''); ?></textarea>
            <br>
            <button type="submit" name="submit_grade">Simpan Nilai</button>
            <button type="submit" class="button-primary">Simpan/Tambah</button>
<a href="javascript:history.back()" class="button-secondary">Kembali</a>
            <a href="index.php?page=incoming_reports" class="button secondary-button">Kembali ke Laporan Masuk</a>
        </form>
    </div>
</div>

<style>
    /* Styling tambahan untuk grade_report.php */
    .report-details-card {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 25px;
        margin-top: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .report-details-card p {
        margin-bottom: 8px;
    }
    .report-details-card strong {
        color: #333;
    }
    .secondary-button {
        background-color: #6c757d;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        text-decoration: none;
        margin-left: 10px;
        display: inline-block; /* Untuk bisa menggunakan margin-left */
    }
    .secondary-button:hover {
        background-color: #5a6268;
    }
</style>