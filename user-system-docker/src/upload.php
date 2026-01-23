<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in first.");
}

$user_id = $_SESSION['user_id'];
$targetDir = "uploads/";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
    $fileName = basename($_FILES['uploaded_file']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'txt'];
    if (!in_array($ext, $allowed)) {
        die("Error: Only JPG, PNG, PDF, or TXT files allowed.");
    }

    //  2MB
    if ($_FILES['uploaded_file']['size'] > 2 * 1024 * 1024) {
        die("Error: File too large (max 2MB).");
    }

    //unique file name
    $newFileName = "user_" . $user_id . "_" . time() . "." . $ext;
    $targetFile = $targetDir . $newFileName;

    // Move the uploaded file
    if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $targetFile)) {
        // Save the path to the database
        $stmt = $conn->prepare("UPDATE users SET profile_picture_path=? WHERE id=?");
        $stmt->bind_param("si", $targetFile, $user_id);
        $stmt->execute();

        echo "File uploaded successfully.<br>";
        echo "<a href='profile.php'>Back to Profile</a>";
    } else {
        echo " Error uploading file.";
    }
} else {
    echo "No file uploaded.";
}
?>
