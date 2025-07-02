<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?? 'SIMPRAK Mahasiswa'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-relaxed tracking-wide flex flex-col min-h-screen">

<!-- Navbar -->
<nav class="bg-blue-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="text-xl font-bold">SIMPRAK Mahasiswa</div>
        <div>
            <span class="mr-4">Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'Mahasiswa'); ?></span>
            <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded text-white text-sm font-medium">Logout</a>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="container mx-auto px-4 py-8">
