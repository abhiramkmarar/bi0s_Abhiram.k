<?php
session_start();
include 'db.php';

// admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// for delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    if ($delete_id > 0) {
        $query = $conn->prepare("DELETE FROM users WHERE id = ?");
        $query->bind_param("i", $delete_id);
        if ($query->execute()) {
            $message = " User with ID $delete_id deleted successfully.";
        } else {
            $message = " Error";
        }
    } else {
        $message = " Invalid user ID.";
    }
}

//all users
$result = $conn->query("SELECT id, username, email, role FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 6px; }
        body { font-family: Arial; }
    </style>
</head>
<body>
    <h2>Manage Users (Admin Only)</h2>
    <a href="user_dashboard.php">⬅ Back to Dashboard</a><br><br>

    <?php if (isset($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <hr>
    <h3>Delete a User</h3>
    <form method="POST">
        Enter User ID to delete: 
        <input type="number" name="delete_id" required>
        <button type="submit">Delete</button>
    </form>
</body>
</html>
