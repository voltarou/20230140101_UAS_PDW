<?php
if (!isAdmin()) {
    redirect('index.php?page=login');
}

// Filter parameters
$filter_modul_id = $_GET['modul_id'] ?? null;
$filter_user_id = $_GET['user_id'] ?? null;
$filter_status = $_GET['status'] ?? null; // 'graded', 'ungraded', 'all'

// Ambil daftar modul untuk filter dropdown
$stmt_modules_filter = $pdo->query("SELECT modul_id, judul_modul FROM Modul ORDER BY judul_modul ASC");
$modules_for_dropdown = $stmt_modules_filter->fetchAll();

// Ambil daftar pengguna (mahasiswa) untuk filter dropdown
$stmt_users_filter = $pdo->query("SELECT user_id, username FROM Users WHERE role = 'mahasiswa' ORDER BY username ASC");
$users_for_dropdown = $stmt_users_filter->fetchAll();

// Build SQL query for reports
$sql = "
    SELECT
        l.laporan_id,
        l.file_laporan,
        l.tanggal_kumpul,
        l.nilai,
        l.feedback,
        u.username AS mahasiswa_username,
        m.judul_modul,
        mp.nama_praktikum
    FROM Laporan l
    JOIN Users u ON l.user_id = u.user_id
    JOIN Modul m ON l.modul_id = m.modul_id
    JOIN MataPraktikum mp ON m.praktikum_id = mp.praktikum_id
    WHERE 1=1
";
$params = [];

if ($filter_modul_id) {
    $sql .= " AND l.modul_id = ?";
    $params[] = $filter_modul_id;
}
if ($filter_user_id) {
    $sql .= " AND l.user_id = ?";
    $params[] = $filter_user_id;
}
if ($filter_status === 'graded') {
    $sql .= " AND l.nilai IS NOT NULL";
} elseif ($filter_status === 'ungraded') {
    $sql .= " AND l.nilai IS NULL";
}

$sql .= " ORDER BY l.tanggal_kumpul DESC";

$stmt_reports = $pdo->prepare($sql);
$stmt_reports->execute($params);
$reports = $stmt_reports->fetchAll();
?>

<div class="container">
    <h2>Laporan Masuk Mahasiswa</h2>

    <div class="filter-section">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="incoming_reports">
            <label for="filter_modul_id">Modul:</label>
            <select id="filter_modul_id" name="modul_id">
                <option value="">-- Semua Modul --</option>
                <?php foreach ($modules_for_dropdown as $mod): ?>
                    <option value="<?php echo $mod['modul_id']; ?>" <?php echo ($filter_modul_id == $mod['modul_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($mod['judul_modul']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="filter_user_id">Mahasiswa:</label>
            <select id="filter_user_id" name="user_id">
                <option value="">-- Semua Mahasiswa --</option>
                <?php foreach ($users_for_dropdown as $usr): ?>
                    <option value="<?php echo $usr['user_id']; ?>" <?php echo ($filter_user_id == $usr['user_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($usr['username']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="filter_status">Status:</label>
            <select id="filter_status" name="status">
                <option value="all" <?php echo ($filter_status === 'all') ? 'selected' : ''; ?>>Semua</option>
                <option value="ungraded" <?php echo ($filter_status === 'ungraded') ? 'selected' : ''; ?>>Belum Dinilai</option>
                <option value="graded" <?php echo ($filter_status === 'graded') ? 'selected' : ''; ?>>Sudah Dinilai</option>
            </select>

            <button type="submit">Filter</button>
            
<a href="javascript:history.back()" class="button-secondary">Kembali</a>
            <a href="index.php?page=incoming_reports" class="button reset-filter">Reset Filter</a>
        </form>
    </div>

    <?php if (empty($reports)): ?>
        <p>Tidak ada laporan yang sesuai dengan filter yang dipilih.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Laporan</th>
                    <th>Mahasiswa</th>
                    <th>Praktikum</th>
                    <th>Modul</th>
                    <th>Tanggal Kumpul</th>
                    <th>Status Nilai</th>
                    <th>Nilai</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo $report['laporan_id']; ?></td>
                        <td><?php echo htmlspecialchars($report['mahasiswa_username']); ?></td>
                        <td><?php echo htmlspecialchars($report['nama_praktikum']); ?></td>
                        <td><?php echo htmlspecialchars($report['judul_modul']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($report['tanggal_kumpul'])); ?></td>
                        <td>
                            <?php if (!is_null($report['nilai'])): ?>
                                <span class="status-badge submitted">Sudah Dinilai</span>
                            <?php else: ?>
                                <span class="status-badge pending">Belum Dinilai</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo is_null($report['nilai']) ? '-' : htmlspecialchars($report['nilai']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($report['file_laporan']); ?>" target="_blank" class="button small">Unduh</a>
                            <a href="index.php?page=grade_report&id=<?php echo $report['laporan_id']; ?>" class="button small edit">Nilai / Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    /* Styling tambahan untuk incoming_reports.php */
    .filter-section {
        background-color: #f0f8ff;
        border: 1px solid #e0f0ff;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .filter-section label {
        font-weight: bold;
        margin-right: 5px;
    }
    .filter-section select, .filter-section button {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
        cursor: pointer;
    }
    .filter-section button {
        background-color: #007bff;
        color: white;
        border: none;
    }
    .filter-section button:hover {
        background-color: #0056b3;
    }
    .filter-section .reset-filter {
        background-color: #6c757d;
        color: white;
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
    }
    .filter-section .reset-filter:hover {
        background-color: #5a6268;
    }
    table .button.small {
        padding: 5px 10px;
        font-size: 0.85em;
        margin-right: 5px;
    }
    table .button.edit {
        background-color: #ffc107;
        color: #333;
    }
    table .button.edit:hover {
        background-color: #e0a800;
    }
</style>