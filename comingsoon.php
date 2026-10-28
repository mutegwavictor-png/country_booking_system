<?php
session_start();
include 'database.php'; // We need the database to show countries

// 1. BOUNCER
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$message = "";

// 2. HANDLE BOOKING
if (isset($_POST['book_country'])) {
    $country_id = intval($_POST['country_id']);

    // Check if they already booked it (optional, but good practice)
    $check = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND country_id = ?");
    $check->bind_param("ii", $user_id, $country_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<div class='error-msg'>You have already booked this country!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, country_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $country_id);
        if ($stmt->execute()) {
            $message = "<div class='success-msg'>Country booked successfully!</div>";
        }
        $stmt->close();
    }
    $check->close();
}

// 3. HANDLE LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// 4. FETCH COUNTRIES TO DISPLAY
$countries = $conn->query("SELECT * FROM country ORDER BY country_name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Book a Country</title>
    <style>
        body {
            margin: 0;
            padding: 40px;
            background: #f4f7f6;
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }

        .logout-btn {
            background: #f8d7da;
            color: #721c24;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .country-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .country-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .country-card h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .country-card p {
            color: #666;
            margin: 0 0 15px 0;
        }

        .book-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }

        .book-btn:hover {
            background: #5568d3;
        }

        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            text-align: center;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header-bar">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <a href="comingsoon.php?logout=true" class="logout-btn">Log Out</a>
        </div>

        <?php echo $message; ?>

        <h2>Available Countries to Book</h2>
        <div class="country-grid">
            <?php
            if ($countries->num_rows > 0) {
                while ($row = $countries->fetch_assoc()) {
                    echo "<div class='country-card'>";
                    echo "<h3>" . htmlspecialchars($row['country_name']) . "</h3>";
                    echo "<p>Capital: " . htmlspecialchars($row['capital']) . "</p>";
                    echo "<form action='comingsoon.php' method='POST'>";
                    echo "<input type='hidden' name='country_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' name='book_country' class='book-btn'>Book Now</button>";
                    echo "</form>";
                    echo "</div>";
                }
            } else {
                echo "<p>No countries available yet. Check back later!</p>";
            }
            $conn->close();
            ?>
        </div>
    </div>

</body>

</html>