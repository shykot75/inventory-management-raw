<?php
include 'config.php';  // Database connection
session_start();  // Start session to store flash messages

if (isset($_POST['submit'])) {
    // Capture form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];
    $status = (int)$_POST['status'];  // Assuming status is always "1"

    // Initialize error array
    $errors = [];

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (strlen($name) > 255) {
        $errors[] = "Name should not exceed 255 characters";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } else {
        // Check if email exists
        $email_check_query = "SELECT * FROM users WHERE email = '$email'";
        $email_check_result = mysqli_query($connection, $email_check_query);
        if (mysqli_num_rows($email_check_result) > 0) {
            $errors[] = "This email already exists";
        }
    }

    if (empty($phone)) {
        $errors[] = "Phone is required";
    } elseif (strlen($phone) != 11 || !ctype_digit($phone)) {
        $errors[] = "Phone number is invalid";
    } else {
        // Check if phone number exists
        $phone_check_query = "SELECT * FROM users WHERE phone = '$phone'";
        $phone_check_result = mysqli_query($connection, $phone_check_query);
        if (mysqli_num_rows($phone_check_result) > 0) {
            $errors[] = "This phone number already exists";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8 || strlen($password) > 25) {
        $errors[] = "Password must be between 8 and 25 characters";
    } elseif ($password !== $password_confirmation) {
        $errors[] = "Passwords do not match";
    }

    // If validation passed, proceed to insert the user into the database
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user data into the database
        $insert_user_query = "INSERT INTO users (name, email, phone, password, status) VALUES ('$name', '$email', '$phone', '$hashed_password', $status)";
        if (mysqli_query($connection, $insert_user_query)) {
            // Registration successful, redirect to login page
            $_SESSION['success'] = "Registration successful. Please login.";
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['error'] = "Oops! Registration failed.";
        }
    } else {
        // Store errors in session
        $_SESSION['errors'] = $errors;
        $_SESSION['old'] = $_POST;  // Store old input values to repopulate the form
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Registration</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    <!-- Local CSS -->
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
<body class="w-full h-screen bg-light text-light dark:bg-dark dark:text-dark">
<!-- Main Content Start -->
<main class="pt-6 px-4 pb-6 text-light bg-primary-100 dark:bg-black dark:text-dark h-full overflow-y-auto">
    <div class="min-w-[calc(100vw - 4rem)] md:w-[30rem] lg:w-[35rem] bg-white card mx-auto drop-shadow-lg">
        <div class="flex flex-col justify-start items-center gap-4 py-6 px-6 relative lg:w-[25rem] mx-auto">
            <div class="h-16 flex justify-center items-center sm:grow md:grow-0">
                <p class="text-2xl flex items-center w-full px-3 text-primary">Inventory Management System</p>
            </div>

            <!-- Include alert message component (already handling errors) -->
            <?php include 'components/login-alert-message.php'; ?>

            <!-- Registration form -->
            <form action="" method="POST" class="w-full">
                <input type="hidden" name="status" value="1">
                <div class="flex flex-col justify-start items-center w-full">
                    <div class="w-full">
                        <label for="name" class="form-input-label text-base">Name</label>
                        <div class="relative">
                            <input type="text" name="name" id="name" class="ltr:pl-10 rtl:pr-10 form-input" placeholder="Enter your name"
                                   value="<?php echo isset($_SESSION['old']['name']) ? htmlspecialchars($_SESSION['old']['name']) : ''; ?>">
                        </div>
                    </div>
                    <div class="w-full">
                        <label for="email" class="form-input-label text-base">Email</label>
                        <div class="relative">
                            <input type="email" name="email" id="username" class="ltr:pl-10 rtl:pr-10 form-input" placeholder="Enter your email"
                                   value="<?php echo isset($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : ''; ?>">
                        </div>
                    </div>
                    <div class="w-full">
                        <label for="phone" class="form-input-label text-base">Phone</label>
                        <div class="relative">
                            <input type="number" name="phone" id="phone" class="ltr:pl-10 rtl:pr-10 form-input"
                                   min="0" oninput="validity.valid||(value='');" onwheel="this.blur()" placeholder="Enter your phone"
                                   value="<?php echo isset($_SESSION['old']['phone']) ? htmlspecialchars($_SESSION['old']['phone']) : ''; ?>">
                        </div>
                    </div>

                    <div class="w-full">
                        <label for="password" class="form-input-label text-base">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" class="ltr:pl-10 rtl:pr-10 form-input" placeholder="Enter your password">
                        </div>
                    </div>
                    <div class="w-full">
                        <label for="password_confirmation" class="form-input-label text-base">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="ltr:pl-10 rtl:pr-10 form-input" placeholder="Re-Enter your password">
                        </div>
                    </div>

                    <div class="w-full">
                        <button type="submit" name="submit" class="btn w-full mt-6 bg-primary text-white font-medium text-lg">Sign Up</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
<!-- Main Content End -->

<!-- Local JavaScript -->
<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>

</body>
</html>

<?php unset($_SESSION['old']); // Clear old data after rendering the form ?>
