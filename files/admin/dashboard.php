<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: ../../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
<h1>Welcome to the Dashboard, <?php echo $_SESSION['user_name']; ?>!</h1>
<p>Your role: <?php echo $_SESSION['user_role']; ?></p>

<a href="../../logout.php">Logout</a>
</body>
</html>
