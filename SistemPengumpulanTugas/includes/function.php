<?php

session_start(); // Start the session at the very beginning

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'asisten';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// Function to handle file uploads
function uploadFile($file, $targetDirectory) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false; // Error during upload
    }

    $fileName = basename($file['name']);
    $targetFilePath = $targetDirectory . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Allow certain file formats (customize as needed)
    $allowTypes = array('pdf', 'doc', 'docx', 'jpg', 'png', 'jpeg', 'gif');
    if (!in_array($fileType, $allowTypes)) {
        return false; // Invalid file type
    }

    // Check if file already exists
    if (file_exists($targetFilePath)) {
        // Optional: rename file to avoid overwrite or return false
        $fileName = time() . '_' . $fileName; // Prepend timestamp
        $targetFilePath = $targetDirectory . $fileName;
    }

    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        return $targetFilePath; // Return the path to the uploaded file
    } else {
        return false; // Failed to move uploaded file
    }
}