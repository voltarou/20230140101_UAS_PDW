<?php
if (!isLoggedIn() || isAdmin()) {
    redirect('index.php?page=login');
}

$user_id = $_SESSION['user_id'];

// Mengambil daftar praktikum yang diikuti oleh mahasiswa ini
$stmt = $pdo->prepare("
    SELECT mp.praktikum_id, mp.nama_praktikum, mp.deskripsi
    FROM MataPraktikum mp
    JOIN PendaftaranPraktikum pp ON mp.praktikum_id = pp.praktikum_id
    WHERE pp.user_id = ?
    ORDER BY mp.nama_praktikum ASC
");
$stmt->execute([$user_id]);
$my_practicums = $stmt->fetchAll();
?>

<div class="container">
    <h2>Praktikum yang Saya Ikuti</h2>
    <p>Berikut adalah daftar mata praktikum yang telah Anda daftarkan.</p>

    <?php if (empty($my_practicums)): ?>
        <p>Anda belum mendaftar ke praktikum apapun. <a href="index.php?page=public_courses">Cari praktikum sekarang!</a></p>
    <?php else: ?>
        <div class="my-practicums-list">
            <?php foreach ($my_practicums as $praktikum): ?>
                <div class="praktikum-item">
                    <h3><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'])); ?></p>
                    <a href="index.php?page=practicum_detail&id=<?php echo $praktikum['praktikum_id']; ?>" class="button view-detail">Lihat Detail & Tugas</a>
                </div>
                
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Contoh Styling untuk my_practicums.php */
    .my-practicums-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 20px;
    }
    .praktikum-item {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; /* Untuk responsif */
    }
    .praktikum-item h3 {
        color: #0056b3;
        margin: 0;
        flex-basis: 100%; /* Judul di baris sendiri di mobile */
        margin-bottom: 5px;
    }
    .praktikum-item p {
        font-size: 0.9em;
        color: #555;
        flex-basis: 100%; /* Deskripsi di baris sendiri di mobile */
        margin-bottom: 10px;
    }
    .praktikum-item .view-detail {
        background-color: #007bff;
        color: white;
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        white-space: nowrap; /* Jangan putus baris */
    }
    .praktikum-item .view-detail:hover {
        background-color: #0056b3;
    }
</style>