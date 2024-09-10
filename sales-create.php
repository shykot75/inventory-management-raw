<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if the user is admin
if ($_SESSION['user_role'] !== 'admin') {
    // If not logged in, redirect to dashboard page
    $_SESSION['error'] = "You don't have permission to access this page";
    header('Location: dashboard.php');
    exit();
}

include 'config.php';  // Include the database connection

// Fetch customers from the database
function getCustomers($connection) {
    $query = "SELECT customer_id, customer_name FROM customers";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}

// Fetch products from the database
function getProducts($connection) {
    $query = "SELECT product_id, product_name, product_price, quantity FROM products";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}

$customers = getCustomers($connection);
$products = getProducts($connection);

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Capture form data
    $sale_date = $_POST['sale_date'];
    $customer_id = $_POST['customer_id'];
    $product_id = $_POST['product_id'];
    $sale_quantity = $_POST['sale_quantity'];
    $sale_total = $_POST['sale_total'];
    $payment_type = $_POST['payment_type'] ?? null;
    $payment_status = $_POST['payment_status'] ?? null;  // Set default value in case it's missing

    // Initialize an errors array
    $errors = [];

    // Validation rules
    if (empty($sale_date)) {
        $errors[] = "Sales date is required";
    }

    if (empty($customer_id) || !is_numeric($customer_id)) {
        $errors[] = "Select a valid customer";
    }

    if (empty($product_id) || !is_numeric($product_id)) {
        $errors[] = "Select a valid product";
    }

    if (empty($sale_quantity) || !is_numeric($sale_quantity)) {
        $errors[] = "Sales quantity is required and should be a valid number";
    }

    if (empty($sale_total) || !is_numeric($sale_total)) {
        $errors[] = "Sales total is required and should be a valid number";
    }

    if (empty($payment_status)) {
        $errors[] = "Payment status is required";
    }

    // If no errors, proceed to insert into database
    if (empty($errors)) {
        // Use traditional mysqli query for insertion
        $insert_query = "INSERT INTO sales (sale_date, customer_id, product_id, sale_quantity, sale_total, payment_type, payment_status)
                         VALUES ('$sale_date', $customer_id, $product_id, $sale_quantity, $sale_total, '$payment_type', '$payment_status')";

        if (mysqli_query($connection, $insert_query)) {
            // Update the product quantity if payment is paid
            if ($payment_status === 'paid') {
                $update_query = "UPDATE products SET quantity = quantity - $sale_quantity WHERE product_id = $product_id";
                mysqli_query($connection, $update_query);
            }

            $_SESSION['success'] = "Sales created successfully";
        } else {
            $_SESSION['error'] = "Error inserting sale into the database";
        }

        // Redirect after form submission
        header("Location: sales-list.php");
        exit();
    } else {
        // Store errors and old inputs in session for display
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
    <title>Sales Create | IMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/libs/flatpickr/flatpickr.min.css">
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

    <!-- Main Content Start -->
    <main class="w-full min-h-full pt-4 px-4 pb-12 text-light dark:bg-black dark:text-dark">

        <?php
        // Replace this with your PHP login alert logic
        include('components/login-alert-message.php');
        ?>

        <div class="main-body">
            <div class="card">
                <div class="card-header">
                    <h6>Create New Sales</h6>
                </div>
                <div class="card-body">
                    <form action="sales-create.php" method="POST">
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <!-- Date -->
                            <div>
                                <label for="sale_date" class="form-input-label text-base font-medium required">Date</label>
                                <input type="text" data-flatpickr name="sale_date" id="basicDataPicker" class="form-input form-date-picker" value="<?php echo isset($_SESSION['old']['sale_date']) ? htmlspecialchars($_SESSION['old']['sale_date']) : ''; ?>" required>
                            </div>

                            <!-- Customer -->
                            <div>
                                <label for="customer_id" class="form-input-label required">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-input form-select" required>
                                    <option selected disabled>--select customer--</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['customer_id']; ?>" <?php echo isset($_SESSION['old']['customer_id']) && $_SESSION['old']['customer_id'] == $customer['customer_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($customer['customer_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Product -->
                            <div>
                                <label for="product_id" class="form-input-label required">Product</label>
                                <select name="product_id" id="product_id" class="form-input form-select" required>
                                    <option selected disabled>--select product--</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['product_price']; ?>" <?php echo isset($_SESSION['old']['product_id']) && $_SESSION['old']['product_id'] == $product['product_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($product['product_name']) . " (Tk-" . $product['product_price'] . ") Q:" . $product['quantity']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid xl:grid-cols-4 gap-x-4 my-2">
                            <!-- Quantity -->
                            <div>
                                <label for="sale_quantity" class="form-input-label required">Quantity</label>
                                <input type="number" name="sale_quantity" id="sale_quantity" class="form-input" value="<?php echo isset($_SESSION['old']['sale_quantity']) ? htmlspecialchars($_SESSION['old']['sale_quantity']) : ''; ?>" required>
                            </div>

                            <!-- Total Price -->
                            <div>
                                <label for="sale_total" class="form-input-label required">Total Sales Price</label>
                                <input type="text" name="sale_total" id="sale_total" class="form-input" readonly
                                       value="<?php echo isset($_SESSION['old']['sale_total']) ? htmlspecialchars($_SESSION['old']['sale_total']) : ''; ?>">
                            </div>

                            <!-- Payment Type -->
                            <div>
                                <label for="payment_type" class="form-input-label">Payment Type</label>
                                <select name="payment_type" id="payment_type" class="form-input form-select">
                                    <option selected disabled>--select payment type--</option>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>

                            <!-- Payment Status -->
                            <div>
                                <label for="payment_status" class="form-input-label required">Payment Status</label>
                                <select name="payment_status" id="payment_status" required class="form-input form-select">
                                    <option selected disabled>--select payment status--</option>
                                    <option value="paid" <?php echo (isset($_SESSION['old']['payment_status']) && $_SESSION['old']['payment_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                                    <option value="unpaid" <?php echo (isset($_SESSION['old']['payment_status']) && $_SESSION['old']['payment_status'] === 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                                </select>
                            </div>
                        </div>

                        <div class="w-full">
                            <button type="submit" name="submit" class="btn bg-primary-500 text-white">Create Sales</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <!-- Main Content End -->
</div>

<?php unset($_SESSION['old']); unset($_SESSION['errors']); // Clear old input values ?>

<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden ease-in-out transition-all duration-500"></div>

<script type="module" src="assets/libs/flatpickr/flatpickr.js"></script>
<script type="module" src="assets/js/date-time-picker.js"></script>
<script>
    document.getElementById('product_id').addEventListener('change', function () {
        let price = this.options[this.selectedIndex].getAttribute('data-price');
        let quantity = document.getElementById('sale_quantity').value;
        document.getElementById('sale_total').value = price * quantity;
    });

    document.getElementById('sale_quantity').addEventListener('input', function () {
        let price = document.getElementById('product_id').options[document.getElementById('product_id').selectedIndex].getAttribute('data-price');
        document.getElementById('sale_total').value = price * this.value;
    });
</script>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>
</body>
</html>
