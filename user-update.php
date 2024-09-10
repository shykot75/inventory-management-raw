<?php
session_start();

// Check if the user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page";
    header('Location: dashboard.php');
    exit();
}

include 'config.php'; // Include the database connection

// Fetch the user based on the ID from the URL
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No user ID provided.";
    header('Location: user-list.php');
    exit();
}

$user_id = $_GET['id'];

// Fetch the existing user details
function getUser($connection, $user_id) {
    $query = "SELECT id, name, email, phone, status FROM users WHERE id = '$user_id' AND deleted_at IS NULL";
    $result = mysqli_query($connection, $query);
    return mysqli_fetch_assoc($result);
}

$user = getUser($connection, $user_id);

// Handle form submission for updating the user
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'] ?? 0;

    $errors = [];

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($phone)) {
        $errors[] = "Phone is required";
    } elseif (strlen($phone) !== 11) {
        $errors[] = "Phone number must be 11 digits";
    }

    // If no errors, proceed to update the database
    if (empty($errors)) {
        $query = "UPDATE users SET name = '$name', email = '$email', phone = '$phone', status = '$status' WHERE id = '$user_id'";
        $update_result = mysqli_query($connection, $query);

        if ($update_result) {
            $_SESSION['success'] = "User updated successfully";
        } else {
            $_SESSION['error'] = "Error updating user: " . mysqli_error($connection);
        }

        // Redirect after submission
        header('Location: user-list.php');
        exit();
    } else {
        // Store errors and old input data in session
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;
    }
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Update | IMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
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
                        <h6 class="text-lg card-title">Update User</h6>
                    </div>
                </div>
                <div class="card-body">
                    <form action="user-update.php?id=<?php echo $user_id; ?>" method="POST">
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <div>
                                <label for="name" class="form-input-label required">Name</label>
                                <input type="text" name="name" id="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                            <div>
                                <label for="email" class="form-input-label required">Email</label>
                                <input type="email" name="email" id="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            <div>
                                <label for="phone" class="form-input-label required">Phone</label>
                                <input type="number" name="phone" id="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            </div>
                        </div>

                        <div class="grid xl:grid-cols-1 gap-x-4 my-2">
                            <div class="form-input-label required">Status</div>
                            <div class="flex gap-x-6 mt-1">
                                <div class="flex items-center py-1 gap-x-4 mr-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" class="form-radio" value="1" <?php echo ($user['status'] == '1') ? 'checked' : ''; ?>>
                                        <span class="ml-2 text-base">Active</span>
                                    </label>
                                </div>
                                <div class="flex items-center py-1 gap-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="status" class="form-radio" value="0" <?php echo ($user['status'] == '0') ? 'checked' : ''; ?>>
                                        <span class="ml-2 text-base">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="w-full">
                            <div class="flex w-full mt-5 justify-end">
                                <button type="submit" name="submit" class="btn bg-primary-500 text-white">Update User</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
</div>

<?php unset($_SESSION['old']); unset($_SESSION['errors']); ?>

<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden ease-in-out transition-all duration-500"></div>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>

</body>
</html>
