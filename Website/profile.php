<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result= $query->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['update_profile'])) {
    $new_username=trim($_POST['new_username']); 
    $new_email=trim($_POST['new_email']); 
//changing user name
    if(!empty($new_username) && !empty($new_email)) {
      $query = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
      $query->bind_param("ssi", $new_username, $new_email, $user_id);
      $query->execute();
      header("Location: profile.php?sucess=profile");
      exit;
    } else {
      $error = "Username caan not be empty";
    }
  }

//changing password
  if(isset($_POST['change_password'])) {
    $new_password=trim($_POST['new_password']));
    if(!empty($new_password)) {
      $query = $conn->prepare("UPDATE users SET password=? WHERE id=?");
      $query->bind_param("si", $new_password, $user_id);
      $query->execute();
      header("Location: profile.php?success=profile");
      exit;
    } else {
      $error = " password can't be empty";
    }
  }

  <!DOCTYPE html>
  <html>
  <head>
      <title>PROFILE PAGE</title>
  </head>
  <body>
    <h2> Welcome <?php echo htmlspecialchars($user['username']); ?> !</h2>
    <p> Your role is :<b> <?php echo htmlspecialchars($user{'role']);?> </b></p>
    <a href="logout.php">Logout</a>
<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <br><br>
    <a href="admin_dashboard.php">Go to Admin Dashboard</a>
    <?php endif; ?>

    <hr>
    <h3>Update Profile Information</h3>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if ((isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <form action="user_dashboard.php" method="post">
        Username: <input type="text" name="new_username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>
        Email: <input type="email" name="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>
        <button type="submit" name="update_profile">Update Profile</button>
    </form>

     <hr>

    <h3>Change Password</h3>
    <form action="user_dashboard.php" method="post">
        New Password: <input type="password" name="new_password" required><br><br>
        <button type="submit" name="update_password">Change Password</button>
    </form>

    <hr>

</body>
</html>
    
