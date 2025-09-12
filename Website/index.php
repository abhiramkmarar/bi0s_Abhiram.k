<?php
session_start(); // Start the session to check if user is logged in
?>
<!DOCTYPE html>
<html>
<head>
    <title>User System Home</title>
</head>
<body>

<h1>Welcome to the User System</h1>

<p>
<?php
// If the user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    echo "<a href='login.php'>Login</a> | <a href='register.php'>Register</a>";
} else {
    // If the user IS logged in
    echo "Welcome, " . $_SESSION['username'] . "! ";
    echo "<a href='profile.php'>Profile</a> | <a href='logout.php'>Logout</a>";

    // If the logged-in user is an admin
    if ($_SESSION['role'] == 'admin') {
        echo " | <a href='admin.php'>Admin Dashboard</a>";
    }
}
?>
</p>

</body>
</html>
