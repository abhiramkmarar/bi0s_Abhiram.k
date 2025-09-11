<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Registration successful. <a href='login.php'>Login here</a>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
}
?>

<form method="post">
  Username: <input type="text" name="username" required><br>
  Email: <input type="email" name="email" required><br>
  Password: <input type="password" name="password" required><br>
  <button type="submit">Register</button>
</form>
