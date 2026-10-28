<?php
session_start();
include 'database.php';

// 1. THE BOUNCER
if (!isset($_SESSION['is_admin'])) {
    header("Location: adminlogin.php");
    exit();
}

$message = "";

// 2. HANDLE DELETE/RESET USERS
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']); 
    $action = $_GET['action'];

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "<div class='success-msg'>User deleted.</div>";
    } 
    elseif ($action === 'reset') {
        $temp_password = "Welcome123"; 
        $hashed = password_hash($temp_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $id);
        $stmt->execute();
        $stmt->close();
        $message = "<div class='success-msg'>Password reset to: <strong>$temp_password</strong></div>";
    }
}

// 3. HANDLE ADDING A COUNTRY
if (isset($_POST['add_country'])) {
    $country_name = trim($_POST['country_name']);
    $capital = trim($_POST['capital']);

    if (!empty($country_name) && !empty($capital)) {
        // Note: We don't insert the ID, the database does it automatically
        $stmt = $conn->prepare("INSERT INTO country (country_name, capital) VALUES (?, ?)");
        $stmt->bind_param("ss", $country_name, $capital);
        if ($stmt->execute()) {
            $message = "<div class='success-msg'>Country <strong>$country_name</strong> added successfully!</div>";
        } else {
            $message = "<div class='error-msg'>Error: " . $conn->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='error-msg'>Please fill in both fields.</div>";
    }
}

// 4. HANDLE LOGOUT
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header("Location: adminlogin.php");
    exit();
}

// 5. FETCH DATA FOR THE TABLES
$users_result = $conn->query("SELECT id, username, email, reg_date FROM users ORDER BY id DESC");
$countries_result = $conn->query("SELECT * FROM country ORDER BY country_name ASC");

// FIXED: We use country.country_id instead of country.id
$bookings_result = $conn->query("SELECT bookings.id, users.username, country.country_name, bookings.booking_date 
                                  FROM bookings 
                                  JOIN users ON bookings.user_id = users.id 
                                  JOIN country ON bookings.country_id = country.country_id 
                                  ORDER BY bookings.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <style>
    body { margin: 0; padding: 40px; background: #f4f7f6; font-family: "Segoe UI", Arial, sans-serif; }
    .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    h1 { color: #333; margin: 0; }
    h2 { color: #444; margin-top: 40px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
    .logout-btn { background: #e74c3c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 6px; font-weight: bold; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #2c3e50; color: white; }
    
    .action-btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; color: white; }
    .btn-reset { background: #f39c12; }
    .btn-delete { background: #e74c3c; }

    /* Styles for the Add Country form */
    .add-country-form { display: flex; gap: 10px; background: #eef2f5; padding: 15px; border-radius: 8px; margin-bottom: 20px;}
    .add-country-form input { padding: 10px; border: 1px solid #ccc; border-radius: 6px; flex: 1; }
    .add-country-form button { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;}

    .success-msg { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #c3e6cb; }
    .error-msg { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
  </style>
</head>
<body>

  <div class="container">
    <div class="header-bar">
      <h1>Admin Dashboard</h1>
      <a href="admindashboard.php?logout=true" class="logout-btn">Admin Logout</a>
    </div>

    <?php echo $message; ?>

    <!-- SECTION 1: ADD COUNTRY -->
    <h2>Add a New Country</h2>
    <form action="admindashboard.php" method="POST" class="add-country-form">
        <input type="text" name="country_name" placeholder="Country Name (e.g. Kenya)" required>
        <input type="text" name="capital" placeholder="Capital City (e.g. Nairobi)" required>
        <button type="submit" name="add_country">Add Country</button>
    </form>

    <!-- SECTION 2: AVAILABLE COUNTRIES -->
    <h2>Available Countries</h2>
    <table>
      <tr><th>ID</th><th>Country</th><th>Capital</th></tr>
      <?php
      if ($countries_result->num_rows > 0) {
          while($row = $countries_result->fetch_assoc()) {
              echo "<tr><td>" . $row['country_id'] . "</td><td><strong>" . htmlspecialchars($row['country_name']) . "</strong></td><td>" . htmlspecialchars($row['capital']) . "</td></tr>";
          }
      } else { echo "<tr><td colspan='3'>No countries added yet.</td></tr>"; }
      ?>
    </table>

    <!-- SECTION 3: USER BOOKINGS -->
    <h2>User Bookings</h2>
    <table>
      <tr><th>Booking ID</th><th>User</th><th>Country Booked</th><th>Date</th></tr>
      <?php
      if ($bookings_result->num_rows > 0) {
          while($row = $bookings_result->fetch_assoc()) {
              echo "<tr>
                      <td>#" . $row['id'] . "</td>
                      <td><strong>" . htmlspecialchars($row['username']) . "</strong></td>
                      <td>" . htmlspecialchars($row['country_name']) . "</td>
                      <td>" . $row['booking_date'] . "</td>
                    </tr>";
          }
      } else { echo "<tr><td colspan='4'>No bookings yet.</td></tr>"; }
      ?>
    </table>

    <!-- SECTION 4: MANAGE USERS -->
    <h2>Manage Users</h2>
    <table>
      <tr><th>ID</th><th>Username</th><th>Email</th><th>Actions</th></tr>
      <?php
      if ($users_result->num_rows > 0) {
          while($row = $users_result->fetch_assoc()) {
              echo "<tr>
                      <td>" . $row['id'] . "</td>
                      <td>" . htmlspecialchars($row['username']) . "</td>
                      <td>" . htmlspecialchars($row['email']) . "</td>
                      <td>
                        <a href='admindashboard.php?action=reset&id=" . $row['id'] . "' class='action-btn btn-reset'>Reset Pass</a>
                        <a href='admindashboard.php?action=delete&id=" . $row['id'] . "' class='action-btn btn-delete'>Delete</a>
                      </td>
                    </tr>";
          }
      } else { echo "<tr><td colspan='4'>No users found.</td></tr>"; }
      $conn->close();
      ?>
    </table>
  </div>
</body>
</html>