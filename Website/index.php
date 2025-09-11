<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<a href='login.php'>Login</a> | <a href='register.php'>Register</a>";
} else {
    echo "Welcome! <a href='profile.php'>Profile</a> | <a href='logout.php'>Logout</a>";

    if ($_SESSION['role'] == 'admin') {
        echo " | <a href='admin.php'>Admin Dashboard</a>";
    }
}
?>
