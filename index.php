<?php
include 'database.php';

$message = ""; // Create an empty variable for our message

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "<div class='error-msg'>Please fill in all fields.</div>";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $use = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        
        if ($use) {
            $use->bind_param("sss", $username, $email, $hashed);
            
            if ($use->execute()) {
                // SUCCESS MESSAGE
                $message = "<div class='success-msg'>Registration successful! You can now <a href='login.php'>log in here</a>.</div>";
            } else {
                $message = "<div class='error-msg'>Error: That username or email is already taken.</div>";
            }
            $use->close();
        } else {
            $message = "<div class='error-msg'>Database Error: " . $conn->error . "</div>";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Login Form</title>
</head>
<body>
    <style>
        body {
          margin: 0;
          min-height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          font-family: "Segoe UI", Arial, sans-serif;
        }

        .logo2 {
          background: #ffffff;
          padding: 40px 35px;
          border-radius: 12px;
          box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
          width: 320px;
        }

        .logo {
          display: flex;
          flex-direction: column;
          gap: 6px;
        }

        .logo label {
          font-size: 14px;
          font-weight: 600;
          color: #333;
          margin-top: 8px;
        }

        .logo input:not([type="submit"]) {
          padding: 10px 12px;
          border: 1px solid #ccc;
          border-radius: 8px;
          font-size: 14px;
          box-sizing: border-box;
          transition: border-color 0.2s, box-shadow 0.2s;
        }

        .logo input:not([type="submit"]):focus {
          outline: none;
          border-color: #667eea;
          box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .logo input.name {
          background-color: #fafafa;
        }

        .logo input[type="submit"] {
          margin-top: 18px;
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

        /* --- NEW STYLES FOR THE MESSAGES --- */
        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            border: 1px solid #c3e6cb;
        }

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
        
        /* --- NEW STYLE FOR THE BOTTOM LINK --- */
        .login-link {
            text-align: center;
            font-size: 13px;
            margin-top: 20px;
            color: #555;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>

  <div class="logo2">
    <!-- THIS IS WHERE THE MESSAGE WILL SHOW UP -->
    <?php echo $message; ?>

    <form action="index.php" method="post" class="logo">

      <label for="username">Username:</label>
      <input type="text" id="username" name="username" class="name" required>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" class="name" required>

      <input type="submit" name="submit" value="Register">

    </form>

    <!-- THIS IS THE NEW LINK TO LOGIN -->
    <div class="login-link">
      Already have an account? <a href="login.php">Log in here</a>
    </div>
  </div>

</body>
</html>