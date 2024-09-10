<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page";
    header('Location: dashboard.php');
    exit();
}

include 'config.php';

// Fetch the user based on the ID from the URL
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No user ID provided.";
    header('Location: user-list.php');
    exit();
}

$user_id = $_GET['id'];

// Handle form submission for password update
if (isset($_POST['submit'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validation
    if (empty($new_password)) {
        $errors[] = "New password is required";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Password should be at least 8 characters";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // If no errors, update the password
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
        $update_result = mysqli_query($connection, $query);

        if ($update_result) {
            $_SESSION['success'] = "Password updated successfully";
        } else {
            $_SESSION['error'] = "Error updating password: " . mysqli_error($connection);
        }

        header('Location: user-list.php');
        exit();
    } else {
        $_SESSION['errors'] = $errors;
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Change Password | IMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }

    </style>
</head>
<body class="w-full bg-body-light text-light dark:bg-dark dark:text-dark">

<!-- Navbar Start -->
<?php include('navbar.php'); ?>
<!-- Navbar End -->


<div class="flex">
    <!-- Sidebar Start -->
    <?php include('sidebar.php'); ?>
    <!-- Sidebar End -->

    <!-- Main Content -->
    <main class="w-full min-h-full pt-4 px-4 pb-12 text-light dark:bg-black dark:text-dark">

        <?php include('components/login-alert-message.php'); ?>

        <div class="main-body">
            <div class="card">
                <div class="card-header rounded-t-md">
                    <div class="flex justify-between items-center gap-x-4">
                        <h6 class="text-lg card-title">Change Password</h6>
                    </div>
                </div>
                <div class="card-body">
                    <form action="user-password-update.php?id=<?php echo $user_id; ?>" method="POST">
                        <div class="grid xl:grid-cols-2 gap-x-4 my-2">
                            <div>
                                <label for="new_password" class="form-input-label required">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-input" placeholder="Enter New Password">
                            </div>
                            <div>
                                <label for="confirm_password" class="form-input-label required">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Re-Enter Password">
                            </div>
                        </div>

                        <div class="w-full">
                            <div class="flex w-full mt-5 justify-end">
                                <button type="submit" name="submit" class="btn bg-primary-500 text-white">Change Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
</div>



<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden ease-in-out transition-all duration-500"></div>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>

</body>
</html>
