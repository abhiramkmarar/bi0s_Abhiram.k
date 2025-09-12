<?php
session_start(); 
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

if (!isset($_SESSION['user_id'])) {
    echo "<a href='login.php'>Login</a> | <a href='register.php'>Register</a>";
} else {

    echo "Welcome, " . $_SESSION['username'] . "! ";
    echo "<a href='profile.php'>Profile</a> | <a href='logout.php'>Logout</a>";

    
    if ($_SESSION['role'] == 'admin') {
        echo " | <a href='admin.php'>Admin Dashboard</a>";
    }
}
?>
</p>

</body>
</html>
