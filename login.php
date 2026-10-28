<?php
// 1. Start the session at the VERY TOP
session_start();

// 2. Connect to the database
include 'database.php';

$message = "";

// 3. Check if the login form was submitted
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 4. Basic validation
    if (empty($username) || empty($password)) {
        $message = "<div class='error-msg'>Please fill in both fields.</div>";
    } else {
        // 5. Look for the user in the database
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // 6. Get the result
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            // User found! Fetch their data
            $user = $result->fetch_assoc();
            
            // 7. Verify the password matches the hash in the database
            if (password_verify($password, $user['password'])) {
                // SUCCESS! Give them the wristband
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username; 

                // Redirect to the coming soon page
                header("Location: comingsoon.php");
                exit();
            } else {
                // Password was wrong
                $message = "<div class='error-msg'>Incorrect password.</div>";
            }
        } else {
            // Username didn't exist
            $message = "<div class='error-msg'>Username not found.</div>";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    /* ---- Page background & centering ---- */
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f4f7f6;
      font-family: "Segoe UI", Arial, sans-serif;
    }

    /* ---- The card ---- */
    .logo2 {
      background: #ffffff;
      padding: 40px 35px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      width: 320px;
    }

    /* ---- The form ---- */
    .logo {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    /* ---- Labels ---- */
    .logo label {
      font-size: 14px;
      font-weight: 600;
      color: #333;
      margin-top: 8px;
    }

    /* ---- Inputs ---- */
    .logo input {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .logo input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    }

    /* ---- Submit button ---- */
    .logo input[type="submit"] {
      margin-top: 20px;
      padding: 12px;
      background: #667eea;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s, transform 0.1s;
    }

    .logo input[type="submit"]:hover {
      background: #5568d3;
    }

    .logo input[type="submit"]:active {
      transform: scale(0.98);
    }

    /* ---- Error Message ---- */
    .error-msg {
      background-color: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      border: 1px solid #f5c6cb;
    }

    /* ---- Link at the bottom ---- */
    .register-link {
      text-align: center;
      font-size: 13px;
      margin-top: 20px;
      color: #555;
    }
    .register-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: bold;
    }
    .register-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="logo2">
    <!-- Display error message if login fails -->
    <?php echo $message; ?>

    <form action="login.php" method="post" class="logo">
      
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <input type="submit" name="login" value="Log In">
      
    </form>

    <div class="register-link">
      Don't have an account? <a href="index.php">Register here</a>
    </div>
  </div>

</body>
</html>