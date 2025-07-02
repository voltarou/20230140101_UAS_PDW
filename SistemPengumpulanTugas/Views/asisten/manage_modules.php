<?php
if (!isAdmin()) {
    redirect('index.php?page=login');
}

$message = '';
$praktikum_id_filter = $_GET['praktikum_id'] ?? null;
$modul_id_edit = $_GET['id'] ?? null;
$editing_module = null;

// Ambil daftar mata praktikum untuk dropdown filter
$stmt_courses = $pdo->query("SELECT praktikum_id, nama_praktikum FROM MataPraktikum ORDER BY nama_praktikum ASC");
$courses_for_dropdown = $stmt_courses->fetchAll();

// Handle Add/Edit Module
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $praktikum_id_post = $_POST['praktikum_id'] ?? null;
    $judul_modul = $_POST['judul_modul'] ?? '';
    $deskripsi_modul = $_POST['deskripsi_modul'] ?? '';
    $modul_id_post = $_POST['modul_id'] ?? null;
    $file_materi = $_FILES['file_materi'] ?? null;

    if (empty($praktikum_id_post) || empty($judul_modul)) {
        $message = "Nama mata praktikum dan judul modul wajib diisi.";
    } else {
        $uploaded_file_path = '';
        if ($file_materi && $file_materi['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/materi/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $uploaded_file_path = uploadFile($file_materi, $upload_dir);
            if (!$uploaded_file_path) {
                $message = "Gagal mengunggah file materi.";
            }
        }

        if (empty($message)) { // Lanjutkan jika tidak ada error upload
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO Modul (praktikum_id, judul_modul, deskripsi_modul, file_materi) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$praktikum_id_post, $judul_modul, $deskripsi_modul, $uploaded_file_path])) {
                    $message = "Modul berhasil ditambahkan.";
                } else {
                    $message = "Gagal menambahkan modul.";
                }
            } elseif ($action === 'edit' && $modul_id_post) {
                // Jika ada file baru diunggah, gunakan path baru, jika tidak, pertahankan yang lama
                $sql = "UPDATE Modul SET praktikum_id = ?, judul_modul = ?, deskripsi_modul = ?";
                $params = [$praktikum_id_post, $judul_modul, $deskripsi_modul];

                if ($uploaded_file_path) {
                    $sql .= ", file_materi = ?";
                    $params[] = $uploaded_file_path;
                }
                $sql .= " WHERE modul_id = ?";
                $params[] = $modul_id_post;

                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($params)) {
                    $message = "Modul berhasil diperbarui.";
                } else {
                    $message = "Gagal memperbarui modul.";
                }
            }
        }
    }
}

// Handle Delete Module
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $modul_id_to_delete = $_GET['id'];
    // Hapus file fisik materi jika ada
    $stmt_file = $pdo->prepare("SELECT file_materi FROM Modul WHERE modul_id = ?");
    $stmt_file->execute([$modul_id_to_delete]);
    $file_to_delete = $stmt_file->fetchColumn();
    if ($file_to_delete && file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }

    $stmt = $pdo->prepare("DELETE FROM Modul WHERE modul_id = ?");
    if ($stmt->execute([$modul_id_to_delete])) {
        $message = "Modul berhasil dihapus.";
        redirect('index.php?page=manage_modules' . ($praktikum_id_filter ? '&praktikum_id=' . $praktikum_id_filter : ''));
    } else {
        $message = "Gagal menghapus modul.";
    }
}

// Fetch module for editing
if ($modul_id_edit && isset($_GET['action']) && $_GET['action'] === 'edit') {
    $stmt = $pdo->prepare("SELECT * FROM Modul WHERE modul_id = ?");
    $stmt->execute([$modul_id_edit]);
    $editing_module = $stmt->fetch();
}

// Fetch all modules (with optional filter)
$sql = "SELECT m.modul_id, m.judul_modul, m.deskripsi_modul, m.file_materi, mp.nama_praktikum
        FROM Modul m
        JOIN MataPraktikum mp ON m.praktikum_id = mp.praktikum_id";
$params = [];

if ($praktikum_id_filter) {
    $sql .= " WHERE m.praktikum_id = ?";
    $params[] = $praktikum_id_filter;
}
$sql .= " ORDER BY mp.nama_praktikum, m.judul_modul ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$modules = $stmt->fetchAll();
?>

<div class="container">
    <h2>Kelola Modul Praktikum</h2>

    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <h3><?php echo $editing_module ? 'Edit' : 'Tambah'; ?> Modul</h3>
    <form action="index.php?page=manage_modules<?php echo $praktikum_id_filter ? '&praktikum_id=' . $praktikum_id_filter : ''; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $editing_module ? 'edit' : 'add'; ?>">
        <?php if ($editing_module): ?>
            <input type="hidden" name="modul_id" value="<?php echo $editing_module['modul_id']; ?>">
        <?php endif; ?>

        <label for="praktikum_id_select">Mata Praktikum:</label>
        <select id="praktikum_id_select" name="praktikum_id" required>
            <option value="">-- Pilih Mata Praktikum --</option>
            <?php foreach ($courses_for_dropdown as $course): ?>
                <option value="<?php echo $course['praktikum_id']; ?>"
                    <?php echo ($editing_module && $editing_module['praktikum_id'] == $course['praktikum_id']) || (!$editing_module && $praktikum_id_filter == $course['praktikum_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($course['nama_praktikum']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="judul_modul">Judul Modul:</label>
        <input type="text" id="judul_modul" name="judul_modul" value="<?php echo $editing_module ? htmlspecialchars($editing_module['judul_modul']) : ''; ?>" required>
        <br>
        <label for="deskripsi_modul">Deskripsi Modul:</label>
        <textarea id="deskripsi_modul" name="deskripsi_modul"><?php echo $editing_module ? htmlspecialchars($editing_module['deskripsi_modul']) : ''; ?></textarea>
        <br>
        <label for="file_materi">File Materi (PDF/DOCX/Gambar):</label>
        <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
        <?php if ($editing_module && $editing_module['file_materi']): ?>
            <p>File saat ini: <a href="<?php echo htmlspecialchars($editing_module['file_materi']); ?>" target="_blank">Lihat File</a> (Unggah baru untuk mengganti)</p>
        <?php endif; ?>
        <br>
        <button type="submit"><?php echo $editing_module ? 'Update' : 'Tambah'; ?></button>
        
<a href="javascript:history.back()" class="button-secondary">Kembali</a>
        <?php if ($editing_module): ?>
            <a href="index.php?page=manage_modules<?php echo $praktikum_id_filter ? '&praktikum_id=' . $praktikum_id_filter : ''; ?>">Batal Edit</a>
        <?php endif; ?>
    </form>

    <h3>Daftar Modul</h3>
    <div class="filter-section">
        <label for="filter_praktikum_id">Filter berdasarkan Mata Praktikum:</label>
        <select id="filter_praktikum_id" onchange="window.location.href = this.value ? 'index.php?page=manage_modules&praktikum_id=' + this.value : 'index.php?page=manage_modules';">
            <option value="">Semua Praktikum</option>
            <?php foreach ($courses_for_dropdown as $course): ?>
                <option value="<?php echo $course['praktikum_id']; ?>" <?php echo ($praktikum_id_filter == $course['praktikum_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($course['nama_praktikum']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if (empty($modules)): ?>
        <p>Belum ada modul yang ditambahkan.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Modul</th>
                    <th>Mata Praktikum</th>
                    <th>Judul Modul</th>
                    <th>Materi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module): ?>
                    <tr>
                        <td><?php echo $module['modul_id']; ?></td>
                        <td><?php echo htmlspecialchars($module['nama_praktikum']); ?></td>
                        <td><?php echo htmlspecialchars($module['judul_modul']); ?></td>
                        <td>
                            <?php if ($module['file_materi']): ?>
                                <a href="<?php echo htmlspecialchars($module['file_materi']); ?>" target="_blank">Unduh</a>
                            <?php else: ?>
                                Tidak ada
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?page=manage_modules&action=edit&id=<?php echo $module['modul_id']; ?><?php echo $praktikum_id_filter ? '&praktikum_id=' . $praktikum_id_filter : ''; ?>">Edit</a> |
                            <a href="index.php?page=manage_modules&action=delete&id=<?php echo $module['modul_id']; ?><?php echo $praktikum_id_filter ? '&praktikum_id=' . $praktikum_id_filter : ''; ?>" class="delete-action" data-confirm-message="Anda yakin ingin menghapus modul ini dan semua laporan terkait?">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    /* Contoh Styling untuk manage_modules.php */
    .filter-section {
        margin-bottom: 20px;
        padding: 15px;
        background-color: #eef;
        border-radius: 8px;
    }
    .filter-section label {
        font-weight: bold;
        margin-right: 10px;
    }
    .filter-section select {
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
</style>