<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in first.");
}

$targetDir = "uploads/"; 
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $fileName = basename($_FILES['uploaded_file']['name']);
    $targetFile = $targetDir . $fileName;

    // Limit (2MB)
    if ($_FILES['uploaded_file']['size'] > 2 * 1024 * 1024) {
        die("Error: File too large. Max size is 2MB.");
    }

    //safe extensions
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'txt'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        die("Error: Only JPG, PNG, PDF, and TXT files allowed.");
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $targetFile)) {
        echo " File uploaded successfully: " . htmlspecialchars($fileName);
    } else {
        echo " Error uploading file.";
    }
} else {
    echo "No file uploaded.";
}
?>
