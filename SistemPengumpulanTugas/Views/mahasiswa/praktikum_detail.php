<?php
if (!isLoggedIn() || isAdmin()) {
    redirect('index.php?page=login');
}

$user_id = $_SESSION['user_id'];
$praktikum_id = $_GET['id'] ?? null;

if (!$praktikum_id) {
    redirect('index.php?page=my_practicums');
}

// Verifikasi apakah mahasiswa terdaftar di praktikum ini
$stmt_check_enrollment = $pdo->prepare("SELECT COUNT(*) FROM PendaftaranPraktikum WHERE user_id = ? AND praktikum_id = ?");
$stmt_check_enrollment->execute([$user_id, $praktikum_id]);
if ($stmt_check_enrollment->fetchColumn() == 0) {
    echo "<div class='container'><p style='color: red;'>Anda tidak terdaftar di praktikum ini.</p></div>";
    // Atau redirect ke halaman daftar praktikum
    // redirect('index.php?page=my_practicums');
    exit();
}

// Ambil detail mata praktikum
$stmt_praktikum = $pdo->prepare("SELECT nama_praktikum, deskripsi FROM MataPraktikum WHERE praktikum_id = ?");
$stmt_praktikum->execute([$praktikum_id]);
$praktikum_detail = $stmt_praktikum->fetch();

if (!$praktikum_detail) {
    echo "<div class='container'><p style='color: red;'>Praktikum tidak ditemukan.</p></div>";
    exit();
}

// Ambil semua modul terkait praktikum ini
$stmt_modules = $pdo->prepare("SELECT modul_id, judul_modul, deskripsi_modul, file_materi FROM Modul WHERE praktikum_id = ? ORDER BY modul_id ASC");
$stmt_modules->execute([$praktikum_id]);
$modules = $stmt_modules->fetchAll();

// Ambil laporan yang sudah dikumpulkan mahasiswa untuk setiap modul
$submitted_reports = [];
$stmt_reports = $pdo->prepare("SELECT modul_id, file_laporan, tanggal_kumpul, nilai, feedback FROM Laporan WHERE user_id = ? AND modul_id IN (SELECT modul_id FROM Modul WHERE praktikum_id = ?)");
$stmt_reports->execute([$user_id, $praktikum_id]);
foreach ($stmt_reports->fetchAll() as $report) {
    $submitted_reports[$report['modul_id']] = $report;
}

// Handle pengumpulan laporan
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $module_id_submit = $_POST['module_id'] ?? null;
    $file_report = $_FILES['report_file'] ?? null;

    if (!$module_id_submit || !$file_report || $file_report['error'] !== UPLOAD_ERR_OK) {
        $message = "<p style='color: red;'>Pastikan modul dipilih dan file laporan diunggah.</p>";
    } else {
        // Tentukan direktori upload
        $upload_dir = 'uploads/laporan/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $uploaded_file_path = uploadFile($file_report, $upload_dir); // Gunakan fungsi uploadFile dari functions.php

        if ($uploaded_file_path) {
            // Cek apakah sudah ada laporan sebelumnya untuk modul ini
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Laporan WHERE user_id = ? AND modul_id = ?");
            $stmt_check->execute([$user_id, $module_id_submit]);

            if ($stmt_check->fetchColumn() > 0) {
                // Update laporan yang sudah ada
                $stmt_update = $pdo->prepare("UPDATE Laporan SET file_laporan = ?, tanggal_kumpul = GETDATE(), nilai = NULL, feedback = NULL WHERE user_id = ? AND modul_id = ?");
                if ($stmt_update->execute([$uploaded_file_path, $user_id, $module_id_submit])) {
                    $message = "<p style='color: green;'>Laporan berhasil diperbarui!</p>";
                } else {
                    $message = "<p style='color: red;'>Gagal memperbarui laporan.</p>";
                }
            } else {
                // Masukkan laporan baru
                $stmt_insert = $pdo->prepare("INSERT INTO Laporan (modul_id, user_id, file_laporan, tanggal_kumpul) VALUES (?, ?, ?, GETDATE())");
                if ($stmt_insert->execute([$module_id_submit, $user_id, $uploaded_file_path])) {
                    $message = "<p style='color: green;'>Laporan berhasil diunggah!</p>";
                } else {
                    $message = "<p style='color: red;'>Gagal mengunggah laporan.</p>";
                }
            }
            // Refresh data laporan setelah upload
            $submitted_reports = [];
            $stmt_reports->execute([$user_id, $praktikum_id]);
            foreach ($stmt_reports->fetchAll() as $report) {
                $submitted_reports[$report['modul_id']] = $report;
            }
        } else {
            $message = "<p style='color: red;'>Gagal mengunggah file. Pastikan format file benar (PDF/DOCX/gambar) dan ukuran tidak terlalu besar.</p>";
        }
    }
}
?>

<div class="container">
    <h2>Detail Praktikum: <?php echo htmlspecialchars($praktikum_detail['nama_praktikum']); ?></h2>
    <p><?php echo nl2br(htmlspecialchars($praktikum_detail['deskripsi'])); ?></p>

    <?php echo $message; // Tampilkan pesan feedback ?>

    <h3>Modul Praktikum</h3>
    <?php if (empty($modules)): ?>
        <p>Belum ada modul yang ditambahkan untuk praktikum ini.</p>
    <?php else: ?>
        <div class="module-list">
            <?php foreach ($modules as $module): ?>
                <div class="module-item">
                    <h4><?php echo htmlspecialchars($module['judul_modul']); ?></h4>
                    <p><?php echo nl2br(htmlspecialchars($module['deskripsi_modul'])); ?></p>
                    <?php if ($module['file_materi']): ?>
                        <p><a href="<?php echo htmlspecialchars($module['file_materi']); ?>" target="_blank" class="download-link">Unduh Materi</a></p>
                    <?php endif; ?>

                    <h5>Pengumpulan Laporan</h5>
                    <?php
                    $report_status = $submitted_reports[$module['modul_id']] ?? null;
                    if ($report_status):
                    ?>
                        <div class="report-status submitted">
                            <p>Status: <span class="status-badge submitted">Sudah Dikumpulkan</span></p>
                            <p>Dikumpulkan pada: <?php echo date('d M Y H:i', strtotime($report_status['tanggal_kumpul'])); ?></p>
                            <p><a href="<?php echo htmlspecialchars($report_status['file_laporan']); ?>" target="_blank" class="download-link">Lihat Laporan Saya</a></p>
                            <?php if (!is_null($report_status['nilai'])): ?>
                                <p>Nilai: <span class="grade-badge"><?php echo htmlspecialchars($report_status['nilai']); ?></span></p>
                                <?php if (!empty($report_status['feedback'])): ?>
                                    <p>Feedback: <em><?php echo nl2br(htmlspecialchars($report_status['feedback'])); ?></em></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p><span class="status-badge pending">Menunggu Penilaian</span></p>
                            <?php endif; ?>
                            <p>
                                <form action="index.php?page=practicum_detail&id=<?php echo $praktikum_id; ?>" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="module_id" value="<?php echo $module['modul_id']; ?>">
                                    <input type="hidden" name="submit_report" value="1">
                                    <label for="report_file_<?php echo $module['modul_id']; ?>">Unggah Ulang Laporan:</label>
                                    <input type="file" id="report_file_<?php echo $module['modul_id']; ?>" name="report_file" accept=".pdf,.doc,.docx,.jpg,.png" required>
                                    <button type="submit">Unggah Ulang</button>
                                </form>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="report-status not-submitted">
                            <p>Status: <span class="status-badge not-submitted">Belum Dikumpulkan</span></p>
                            <form action="index.php?page=practicum_detail&id=<?php echo $praktikum_id; ?>" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="module_id" value="<?php echo $module['modul_id']; ?>">
                                <input type="hidden" name="submit_report" value="1">
                                <label for="report_file_<?php echo $module['modul_id']; ?>">Unggah Laporan:</label>
                                <input type="file" id="report_file_<?php echo $module['modul_id']; ?>" name="report_file" accept=".pdf,.doc,.docx,.jpg,.png" required>
                                <button type="submit">Unggah Laporan</button>
                                
<a href="javascript:history.back()" class="button-secondary">Kembali</a>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Contoh Styling untuk praktikum_detail.php */
    .module-list {
        margin-top: 20px;
    }
    .module-item {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .module-item h4 {
        color: #0056b3;
        margin-top: 0;
        margin-bottom: 10px;
        border-bottom: 1px dashed #eee;
        padding-bottom: 5px;
    }
    .download-link {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.9em;
        margin-top: 10px;
    }
    .download-link:hover {
        background-color: #0056b3;
    }
    .report-status {
        margin-top: 15px;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    .report-status.submitted {
        background-color: #e9ffe9;
        border-color: #28a745;
    }
    .report-status.not-submitted {
        background-color: #fff0f0;
        border-color: #dc3545;
    }
    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-weight: bold;
        font-size: 0.8em;
        color: white;
    }
    .status-badge.submitted { background-color: #28a745; }
    .status-badge.not-submitted { background-color: #dc3545; }
    .status-badge.pending { background-color: #ffc107; color: #333; }
    .grade-badge {
        font-size: 1.2em;
        font-weight: bold;
        color: #0056b3;
    }
    .report-status form {
        margin-top: 10px;
    }
    .report-status form input[type="file"] {
        margin-bottom: 5px;
    }
</style>