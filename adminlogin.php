<?php
session_start();

// --- NEW: IP BLOCKING LOGIC ---
include 'ip_blocklist.php';
$user_ip = $_SERVER['REMOTE_ADDR'];

if (in_array($user_ip, $blocked_ips)) {
    // Stop the page immediately and show a message
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif; color:red;'>
            <h1>403 Forbidden</h1>
            <p>Your IP address ($user_ip) has been blocked from accessing this page.</p>
         </div>");
}
// ------------------------------

// If they are already logged in as admin, send them to the dashboard
if (isset($_SESSION['is_admin'])) {
    header("Location: admindashboard.php");
    exit();
}

$message = "";

if (isset($_POST['admin_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['is_admin'] = true;
        header("Location: admindashboard.php");
        exit();
    } else {
        $message = "<div class='error-msg'>Invalid Admin Credentials.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f7f6; font-family: "Segoe UI", Arial, sans-serif; }
        .logo2 { background: #ffffff; padding: 40px 35px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); width: 320px; }
        .logo { display: flex; flex-direction: column; gap: 6px; }
        .logo label { font-size: 14px; font-weight: 600; color: #333; margin-top: 8px; }
        .logo input { padding: 10px 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .logo input[type="submit"] { margin-top: 20px; padding: 12px; background: #e74c3c; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        .logo input[type="submit"]:hover { background: #c0392b; }
        .error-msg { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="logo2">
        <h2 style="text-align:center; color:#333; margin-top:0;">Admin Panel</h2>
        <?php echo $message; ?>
        <!-- Make sure the action matches your exact file name -->
        <form action="adminlogin.php" method="post" class="logo">
            <label>Admin Username:</label>
            <input type="text" name="username" required>
            <label>Admin Password:</label>
            <input type="password" name="password" required>
            <input type="submit" name="admin_login" value="Secure Login">
        </form>
    </div>
</body>
</html>