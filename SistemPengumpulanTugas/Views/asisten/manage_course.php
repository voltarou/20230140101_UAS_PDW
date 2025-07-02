<?php
// views/asisten/manage_course.php

// Pastikan hanya asisten yang bisa mengakses halaman ini
if (!isAdmin()) {
    redirect('index.php?page=login');
}

// Inisialisasi pesan
$message = '';
$message_type = ''; // 'success' or 'error'

// Handle Tambah (Add) Mata Praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $nama_praktikum = trim($_POST['nama_praktikum'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (!empty($nama_praktikum) && !empty($deskripsi)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO MataPraktikum (nama_praktikum, deskripsi) VALUES (?, ?)");
            if ($stmt->execute([$nama_praktikum, $deskripsi])) {
                $message = "Mata praktikum berhasil ditambahkan!";
                $message_type = 'success';
            } else {
                $message = "Gagal menambahkan mata praktikum.";
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = "Error database: " . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = "Nama Praktikum dan Deskripsi tidak boleh kosong.";
        $message_type = 'error';
    }
}

// Handle Edit (Update) Mata Praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_course') {
    $praktikum_id = $_POST['praktikum_id'] ?? null;
    $nama_praktikum = trim($_POST['nama_praktikum'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($praktikum_id && !empty($nama_praktikum) && !empty($deskripsi)) {
        try {
            $stmt = $pdo->prepare("UPDATE MataPraktikum SET nama_praktikum = ?, deskripsi = ? WHERE praktikum_id = ?");
            if ($stmt->execute([$nama_praktikum, $deskripsi, $praktikum_id])) {
                $message = "Mata praktikum berhasil diperbarui!";
                $message_type = 'success';
            } else {
                $message = "Gagal memperbarui mata praktikum.";
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = "Error database: " . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = "ID Praktikum, Nama Praktikum, dan Deskripsi tidak boleh kosong.";
        $message_type = 'error';
    }
}

// Handle Hapus (Delete) Mata Praktikum
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete_course') {
    $praktikum_id = $_GET['id'] ?? null;

    if ($praktikum_id) {
        try {
            // Cek apakah ada modul atau pendaftaran terkait sebelum menghapus
            $stmt_check_modules = $pdo->prepare("SELECT COUNT(*) FROM Modul WHERE praktikum_id = ?");
            $stmt_check_modules->execute([$praktikum_id]);
            $has_modules = $stmt_check_modules->fetchColumn() > 0;

            $stmt_check_registrations = $pdo->prepare("SELECT COUNT(*) FROM PendaftaranPraktikum WHERE praktikum_id = ?");
            $stmt_check_registrations->execute([$praktikum_id]);
            $has_registrations = $stmt_check_registrations->fetchColumn() > 0;

            if ($has_modules || $has_registrations) {
                $message = "Tidak dapat menghapus mata praktikum karena ada modul atau pendaftaran terkait. Hapus terlebih dahulu modul dan pendaftaran yang berkaitan.";
                $message_type = 'error';
            } else {
                $stmt = $pdo->prepare("DELETE FROM MataPraktikum WHERE praktikum_id = ?");
                if ($stmt->execute([$praktikum_id])) {
                    $message = "Mata praktikum berhasil dihapus!";
                    $message_type = 'success';
                } else {
                    $message = "Gagal menghapus mata praktikum.";
                    $message_type = 'error';
                }
            }
        } catch (PDOException $e) {
            $message = "Error database: " . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = "ID Praktikum tidak ditemukan untuk dihapus.";
        $message_type = 'error';
    }
}


// Fetch all Mata Praktikum for display
try {
    $stmt = $pdo->query("SELECT praktikum_id, nama_praktikum, deskripsi FROM MataPraktikum ORDER BY praktikum_id DESC");
    $mata_praktikum = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<div class='container'><p style='color: red;'>Error mengambil data mata praktikum: " . $e->getMessage() . "</p></div>";
    $mata_praktikum = [];
}

// Get data for editing if 'edit' action is requested
$edit_data = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT praktikum_id, nama_praktikum, deskripsi FROM MataPraktikum WHERE praktikum_id = ?");
        $stmt->execute([$edit_id]);
        $edit_data = $stmt->fetch();
        if (!$edit_data) {
            $message = "Data praktikum tidak ditemukan untuk diedit.";
            $message_type = 'error';
        }
    } catch (PDOException $e) {
        $message = "Error mengambil data edit: " . $e->getMessage();
        $message_type = 'error';
    }
}
?>

<div class="container">
    <h2>Kelola Mata Praktikum</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <h3><?php echo $edit_data ? 'Edit Mata Praktikum' : 'Tambah Mata Praktikum'; ?></h3>
    <form action="index.php?page=manage_courses" method="POST">
        <?php if ($edit_data): ?>
            <input type="hidden" name="action" value="edit_course">
            <input type="hidden" name="praktikum_id" value="<?php echo htmlspecialchars($edit_data['praktikum_id']); ?>">
        <?php else: ?>
            <input type="hidden" name="action" value="add_course">
        <?php endif; ?>

        <div class="form-group">
            <label for="nama_praktikum">Nama Mata Praktikum:</label>
            <input type="text" id="nama_praktikum" name="nama_praktikum" value="<?php echo htmlspecialchars($edit_data['nama_praktikum'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi:</label>
            <textarea id="deskripsi" name="deskripsi" required><?php echo htmlspecialchars($edit_data['deskripsi'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="button-primary"><?php echo $edit_data ? 'Simpan Perubahan' : 'Tambah'; ?></button>
        <!-- Tombol Kembali -->
        <a href="javascript:history.back()" class="button-secondary">Kembali</a>
    </form>

    <hr style="margin: 30px 0;">

    <h3>Daftar Mata Praktikum</h3>
    <?php if (empty($mata_praktikum)): ?>
        <p>Belum ada mata praktikum yang terdaftar.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Praktikum</th>
                    <th>Nama Praktikum</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mata_praktikum as $praktikum): ?>
                    <tr>
                        <td data-label="ID Praktikum"><?php echo htmlspecialchars($praktikum['praktikum_id']); ?></td>
                        <td data-label="Nama Praktikum"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></td>
                        <td data-label="Deskripsi"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></td>
                        <td data-label="Aksi" class="action-links">
                            <a href="index.php?page=manage_courses&action=edit&id=<?php echo htmlspecialchars($praktikum['praktikum_id']); ?>">Edit</a> |
                            <a href="index.php?page=manage_courses&action=delete_course&id=<?php echo htmlspecialchars($praktikum['praktikum_id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus praktikum ini? Ini akan menghapus modul dan pendaftaran terkait!');" class="delete">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
