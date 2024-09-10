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

// Fetch sale details based on sale_id from URL
if (!isset($_GET['sale_id'])) {
    $_SESSION['error'] = 'No sale ID provided.';
    header('Location: sales-list.php');
    exit();
}

$sale_id = $_GET['sale_id'];

// Fetch the sale details using mysqli_query
function getSales($connection, $sale_id) {
    $query = "SELECT s.sale_id, s.product_id, s.sale_quantity, s.sale_total, s.sale_date, 
                     s.payment_status, prod.product_name, prod.product_price, prod.quantity as current_stock 
              FROM sales s 
              JOIN products prod ON s.product_id = prod.product_id 
              WHERE s.sale_id = $sale_id LIMIT 1";

    $result = mysqli_query($connection, $query);
    return mysqli_fetch_assoc($result);
}

$sale = getSales($connection, $sale_id);

// Handle form submission for sale return
if (isset($_POST['submit'])) {
    $return_date = $_POST['return_date'];
    $return_quantity = $_POST['return_quantity'];
    $return_amount = $_POST['return_amount'];
    $payment_type = $_POST['payment_type'] ?? null;
    $payment_status = $_POST['payment_status'] ?? null;
    $return_reason = $_POST['return_reason'];
    $sale_id = $_POST['sale_id'];
    $product_id = $_POST['product_id'];

    // Initialize errors array
    $errors = [];

    // Validation rules
    if (empty($return_date)) {
        $errors[] = "Return date is required";
    }
    if (empty($return_quantity) || !is_numeric($return_quantity)) {
        $errors[] = "Return quantity is required and must be a valid number";
    }
    if (empty($return_amount) || !is_numeric($return_amount)) {
        $errors[] = "Return amount is required and must be a valid number";
    }
    if (empty($payment_status)) {
        $errors[] = "Payment status is required";
    }

    // Proceed if no errors
    if (empty($errors)) {
        // Insert return data into sale_returns table
        $query = "INSERT INTO sale_returns (sale_id, product_id, return_date, return_quantity, return_amount, payment_type, payment_status, return_reason) 
                  VALUES ('$sale_id', '$product_id', '$return_date', '$return_quantity', '$return_amount', '$payment_type', '$payment_status', '$return_reason')";
        $result = mysqli_query($connection, $query);

        if ($result) {
            // Update the product quantity after return
            $update_query = "UPDATE products SET quantity = quantity + $return_quantity WHERE product_id = $product_id";
            mysqli_query($connection, $update_query);

            // Update sale table that product has returned
            $update_sale = "UPDATE sales SET is_returned = 1 WHERE sale_id = $sale_id";
            mysqli_query($connection, $update_sale);

            $_SESSION['success'] = "Sales return processed successfully";
        } else {
            $_SESSION['error'] = "Error processing sale return";
        }

        // Redirect after form submission
        header("Location: sales-return-list.php");
        exit();
    } else {
        // Store errors and old input values in session
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
    <title>Sales Return | IMS</title>
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
                    <h6>Create Sales Return</h6>
                </div>
                <div class="card-body">
                    <form action="sales-return.php?sale_id=<?php echo $sale_id; ?>" method="POST">
                        <input type="hidden" name="sale_id" value="<?php echo $sale['sale_id']; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $sale['product_id']; ?>">

                        <!-- Return Date -->
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <div>
                                <label for="return_date" class="form-input-label required">Date</label>
                                <input type="text" name="return_date" id="basicDataPicker" class="form-input form-date-picker" data-flatpickr value="<?php echo isset($_SESSION['old']['return_date']) ? htmlspecialchars($_SESSION['old']['return_date']) : ''; ?>" required>
                            </div>

                            <!-- Product -->
                            <div>
                                <label for="product_id" class="form-input-label required">Product</label>
                                <input type="text" id="product_id" class="form-input" value="<?php echo htmlspecialchars($sale['product_name']) . " (Tk-" . htmlspecialchars($sale['product_price']) . ") CS:" . htmlspecialchars($sale['current_stock']); ?>" readonly>
                            </div>

                            <!-- Return Quantity -->
                            <div>
                                <label for="return_quantity" class="form-input-label required">Return Quantity</label>
                                <input type="number" name="return_quantity" id="return_quantity" class="form-input" value="<?php echo htmlspecialchars($sale['sale_quantity']); ?>" readonly required>
                            </div>
                        </div>

                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <!-- Return Amount -->
                            <div>
                                <label for="return_amount" class="form-input-label required">Return Amount</label>
                                <input type="text" name="return_amount" id="return_amount" class="form-input" value="<?php echo htmlspecialchars($sale['sale_total']); ?>" readonly required>
                            </div>

                            <!-- Payment Type -->
                            <div>
                                <label for="payment_type" class="form-input-label required">Payment Type</label>
                                <select name="payment_type" id="payment_type" class="form-input form-select" required>
                                    <option selected disabled>--select payment type--</option>
                                    <option value="cash" <?php echo isset($_SESSION['old']['payment_type']) && $_SESSION['old']['payment_type'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                    <option value="card" <?php echo isset($_SESSION['old']['payment_type']) && $_SESSION['old']['payment_type'] == 'card' ? 'selected' : ''; ?>>Card</option>
                                </select>
                            </div>

                            <!-- Payment Status -->
                            <div>
                                <label for="payment_status" class="form-input-label required">Payment Status</label>
                                <select name="payment_status" id="payment_status" class="form-input form-select" required>
                                    <option selected disabled>--select payment status--</option>
                                    <option value="paid" <?php echo isset($_SESSION['old']['payment_status']) && $_SESSION['old']['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="unpaid" <?php echo isset($_SESSION['old']['payment_status']) && $_SESSION['old']['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                                </select>
                            </div>
                        </div>

                        <!-- Return Reason -->
                        <div class="grid xl:grid-cols-1 gap-x-4 my-2">
                            <div>
                                <label for="return_reason" class="form-input-label">Return Reason</label>
                                <textarea name="return_reason" id="return_reason" class="form-input" rows="4" placeholder="Write a return reason"><?php echo isset($_SESSION['old']['return_reason']) ? htmlspecialchars($_SESSION['old']['return_reason']) : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="w-full">
                            <button type="submit" name="submit" class="btn bg-primary-500 text-white">Create Sales Return</button>
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
    document.getElementById('sale_id').addEventListener('change', function () {
        let price = parseFloat(this.options[this.selectedIndex].getAttribute('data-price'));
        let saleQuantity = parseInt(this.options[this.selectedIndex].getAttribute('data-quantity'));
        let returnQuantity = document.getElementById('return_quantity').value;

        if(returnQuantity) {
            let returnAmount = (returnQuantity / saleQuantity) * price * saleQuantity;
            document.getElementById('return_amount').value = returnAmount.toFixed(2);
        }
    });

    document.getElementById('return_quantity').addEventListener('input', function () {
        let price = parseFloat(document.getElementById('sale_id').options[document.getElementById('sale_id').selectedIndex].getAttribute('data-price'));
        let saleQuantity = parseInt(document.getElementById('sale_id').options[document.getElementById('sale_id').selectedIndex].getAttribute('data-quantity'));
        let returnAmount = (this.value / saleQuantity) * price * saleQuantity;
        document.getElementById('return_amount').value = returnAmount.toFixed(2);
    });
</script>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>
</body>
</html>
