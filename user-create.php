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

include 'config.php';  // Include the database connection


// Handle form submission
if (isset($_POST['submit'])) {
    // Capture form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'] ?? 0;
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    $errors = [];

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $email_query = "SELECT id FROM users WHERE email = '$email' AND deleted_at IS NULL";
        $email_result = mysqli_query($connection, $email_query);
        if (mysqli_num_rows($email_result) > 0) {
            $errors[] = "Email already exists";
        }
    }

    if (empty($phone)) {
        $errors[] = "Phone is required";
    } elseif (strlen($phone) !== 11) {
        $errors[] = "Phone number must be 11 digits";
    } else {
        // Check if phone already exists
        $phone_query = "SELECT id FROM users WHERE phone = '$phone' AND deleted_at IS NULL";
        $phone_result = mysqli_query($connection, $phone_query);
        if (mysqli_num_rows($phone_result) > 0) {
            $errors[] = "Phone number already exists";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password should be at least 8 characters";
    } elseif ($password !== $password_confirmation) {
        $errors[] = "Passwords do not match";
    }

    // If no errors, proceed to insert into the database
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO users (name, email, phone, password, status) 
                  VALUES ('$name', '$email', '$phone', '$hashed_password', '$status')";
        $insert_result = mysqli_query($connection, $query);

        if ($insert_result) {
            $_SESSION['success'] = "User created successfully";
        } else {
            $_SESSION['error'] = "Error inserting user: " . mysqli_error($connection);
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
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>User Create | IMS</title>
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

<!-- Navbar -->
<?php include('navbar.php'); ?>

<div class="flex">
    <!-- Sidebar -->
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <main class="w-full min-h-full pt-4 px-4 pb-12">
        <?php include('components/login-alert-message.php'); ?>

        <div class="main-body">
            <div class="card">
                <div class="card-header rounded-t-md">
                    <div class="flex justify-between items-center gap-x-4">
                        <h6 class="text-lg card-title">Add New User</h6>
                    </div>
                </div>
                <div class="card-body">
                    <form action="user-create.php" method="POST">
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <div>
                                <label for="name" class="form-input-label required">Name</label>
                                <input type="text" name="name" id="name" class="form-input" placeholder="Enter Name"
                                       value="<?php echo isset($_SESSION['old']['name']) ? htmlspecialchars($_SESSION['old']['name']) : ''; ?>">
                            </div>
                            <div>
                                <label for="email" class="form-input-label required">Email</label>
                                <input type="email" name="email" id="email" class="form-input" placeholder="Enter Email"
                                       value="<?php echo isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : ''; ?>">
                            </div>
                            <div>
                                <label for="phone" class="form-input-label required">Phone</label>
                                <input type="number" name="phone" id="phone" class="form-input" placeholder="Enter Phone"
                                       value="<?php echo isset($_SESSION['old']['phone']) ? htmlspecialchars($_SESSION['old']['phone']) : ''; ?>">
                            </div>
                        </div>

                        <div class="grid xl:grid-cols-2 gap-x-4 my-2">
                            <div>
                                <label for="password" class="form-input-label required">Password</label>
                                <input type="password" name="password" id="password" class="form-input" placeholder="Enter Password">
                            </div>
                            <div>
                                <label for="password_confirmation" class="form-input-label required">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" placeholder="Re-Enter Password">
                            </div>
                        </div>

                        <div class="grid xl:grid-cols-1 gap-x-4 my-2">
                            <div>
                                <label class="form-input-label text-base font-medium required">Status</label>
                                <div class="flex gap-x-2 mt-1">
                                    <div class="flex items-center py-1 gap-x-4 mr-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status" class="border rounded-full appearance-none size-4 bg-slate-100 border-slate-200 dark:bg-zinc-600 dark:border-zinc-500 checked:bg-primary-500 checked:border-primary-500 dark:checked:bg-primary-500 dark:checked:border-primary-500"
                                                   value="1" <?php echo isset($_SESSION['old']['status']) && $_SESSION['old']['status'] == '1' ? 'checked' : ''; ?>>
                                            <span class="ml-2 text-base">Active</span>
                                        </label>
                                    </div>
                                    <div class="flex items-center py-1 gap-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status" class="border rounded-full appearance-none size-4 bg-slate-100 border-slate-200 dark:bg-zinc-600 dark:border-zinc-500 checked:bg-primary-500"
                                                   value="0" <?php echo !isset($_SESSION['old']['status']) || $_SESSION['old']['status'] == '0' ? 'checked' : ''; ?>>
                                            <span class="ml-2 text-base">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full">
                            <div class="flex w-full mt-5 justify-end">
                                <button type="submit" name="submit" class="btn bg-primary-500 text-white">Save User</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php unset($_SESSION['old']); unset($_SESSION['errors']); // Clear old input values ?>

<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden ease-in-out transition-all duration-500"></div>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>
</body>
</html>
