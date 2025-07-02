<?php
if (!isAdmin()) {
    redirect('index.php?page=login');
}

$message = '';
$user_id_edit = $_GET['id'] ?? null;
$editing_user = null;

// Handle Add/Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_id_post = $_POST['user_id'] ?? null;

    if (empty($username) || empty($email) || empty($role)) {
        $message = "Username, email, dan role wajib diisi.";
    } elseif ($action === 'add' && empty($password)) {
        $message = "Password wajib diisi untuk user baru.";
    } else {
        // Cek duplikasi username/email (kecuali saat mengedit diri sendiri)
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE (username = ? OR email = ?) AND user_id != ?");
        $stmt_check->execute([$username, $email, $user_id_post ?? 0]);
        if ($stmt_check->fetchColumn() > 0) {
            $message = "Username atau email sudah digunakan oleh pengguna lain.";
        } else {
            if ($action === 'add') {
                $hashedPassword = hashPassword($password);
                $stmt = $pdo->prepare("INSERT INTO Users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashedPassword, $role])) {
                    $message = "Pengguna berhasil ditambahkan.";
                } else {
                    $message = "Gagal menambahkan pengguna.";
                }
            } elseif ($action === 'edit' && $user_id_post) {
                $sql = "UPDATE Users SET username = ?, email = ?, role = ?";
                $params = [$username, $email, $role];

                if (!empty($password)) { // Hanya update password jika diisi
                    $hashedPassword = hashPassword($password);
                    $sql .= ", password_hash = ?";
                    $params[] = $hashedPassword;
                }
                $sql .= " WHERE user_id = ?";
                $params[] = $user_id_post;

                $stmt = $pdo->prepare($sql);
                if ($stmt->execute($params)) {
                    $message = "Pengguna berhasil diperbarui.";
                } else {
                    $message = "Gagal memperbarui pengguna.";
                }
            }
        }
    }
}

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = $_GET['id'];
    // Cegah admin menghapus dirinya sendiri
    if ($user_id_to_delete == $_SESSION['user_id']) {
        $message = "Anda tidak bisa menghapus akun Anda sendiri.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
        if ($stmt->execute([$user_id_to_delete])) {
            $message = "Pengguna berhasil dihapus.";
            redirect('index.php?page=manage_users');
        } else {
            $message = "Gagal menghapus pengguna.";
        }
    }
}

// Fetch user for editing
if ($user_id_edit && isset($_GET['action']) && $_GET['action'] === 'edit') {
    $stmt = $pdo->prepare("SELECT user_id, username, email, role FROM Users WHERE user_id = ?");
    $stmt->execute([$user_id_edit]);
    $editing_user = $stmt->fetch();
}

// Fetch all users
$stmt = $pdo->query("SELECT user_id, username, email, role FROM Users ORDER BY username ASC");
$users = $stmt->fetchAll();
?>

<div class="container">
    <h2>Kelola Akun Pengguna</h2>

    <?php if ($message): ?>
        <p style="color: green;"><?php echo $message; ?></p>
    <?php endif; ?>

    <h3><?php echo $editing_user ? 'Edit' : 'Tambah'; ?> Pengguna</h3>
    <form action="index.php?page=manage_users" method="POST">
        <input type="hidden" name="action" value="<?php echo $editing_user ? 'edit' : 'add'; ?>">
        <?php if ($editing_user): ?>
            <input type="hidden" name="user_id" value="<?php echo $editing_user['user_id']; ?>">
        <?php endif; ?>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $editing_user ? htmlspecialchars($editing_user['username']) : ''; ?>" required>
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $editing_user ? htmlspecialchars($editing_user['email']) : ''; ?>" required>
        <br>
        <label for="role">Role:</label>
        <select id="role" name="role" required>
            <option value="mahasiswa" <?php echo ($editing_user && $editing_user['role'] === 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
            <option value="asisten" <?php echo ($editing_user && $editing_user['role'] === 'asisten') ? 'selected' : ''; ?>>Asisten</option>
        </select>
        <br>
        <label for="password">Password <?php echo $editing_user ? '(kosongkan jika tidak ingin mengubah)' : ''; ?>:</label>
        <input type="password" id="password" name="password">
        <br>
        <button type="submit"><?php echo $editing_user ? 'Update' : 'Tambah'; ?></button>
        
        <a href="javascript:history.back()" class="button-secondary">Kembali</a>
        <?php if ($editing_user): ?>
            <a href="index.php?page=manage_users">Batal Edit</a>
        <?php endif; ?>
    </form>

    <h3>Daftar Pengguna</h3>
    <?php if (empty($users)): ?>
        <p>Belum ada pengguna terdaftar.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Pengguna</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <a href="index.php?page=manage_users&action=edit&id=<?php echo $user['user_id']; ?>">Edit</a> |
                            <a href="index.php?page=manage_users&action=delete&id=<?php echo $user['user_id']; ?>" class="delete-action" data-confirm-message="Anda yakin ingin menghapus pengguna ini?">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>