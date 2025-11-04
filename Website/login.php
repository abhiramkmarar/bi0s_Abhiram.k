<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, role, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if ($user['password'] === $password) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            	
            exit();
        }
       
    }
    echo "Invalid email or password";
}
?>

<form method="post">
  Email: <input type="email" name="email  " required><br>
  Password: <input type="password" name="password" required><br>
  <button type="submit">LOGIN</button>
</form>
<style>
	body {
	background-color : lightblue;
		}
</style>

