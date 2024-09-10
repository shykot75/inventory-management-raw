<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';  // Include the database connection

// Fetch all sales from the database
function getSales($connection) {
    $query = "SELECT s.sale_id, s.sale_date, s.sale_quantity, s.sale_total, s.payment_status, s.is_returned,
                     prod.product_name, prod.product_price, prod.quantity as current_stock,
                     cus.customer_name
              FROM sales s
              JOIN products prod ON s.product_id = prod.product_id
              JOIN customers cus ON s.customer_id = cus.customer_id
              WHERE s.deleted_at IS NULL
              ORDER BY s.sale_date DESC";

    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    return [];
}

$sales = getSales($connection);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Purhchase List | IMS</title>
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

    <!-- Main Content Start -->
    <main class="w-full min-h-full pt-4 px-4 pb-12 text-light dark:bg-black dark:text-dark">

        <?php
        // Replace this with your PHP login alert logic
        include('components/login-alert-message.php');
        ?>

        <div class="main-body">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-4 text-15">Sales List</h6>
                    <!-- Add responsive table wrapper -->
                    <div class="responsive-table">
                        <table id="borderedTable" class="bordered group" style="width:100%">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Sales Date</th>
                                <th>Product Name</th>
                                <th>Customer</th>
                                <th>Quantity</th>
                                <th>Total (Tk)</th>
                                <th>Payment</th>
                                <th>Returned?</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($sales) > 0): ?>
                                <?php foreach ($sales as $index => $sale): ?>
                                    <tr class="text-center">
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($sale['product_name']); ?> <br>
                                            <small>Price: <?php echo htmlspecialchars($sale['product_price']); ?> Tk.</small>
                                            <small>CS: <?php echo htmlspecialchars($sale['current_stock']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['sale_quantity']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($sale['sale_total'], 2)); ?></td>
                                        <td>
                                            <div class="<?php echo $sale['payment_status'] === 'paid' ? 'badge-active' : 'badge-inactive'; ?>  px-2.5 py-0.5 inline-block">
                                                <?php echo $sale['payment_status'] === 'paid' ? 'Paid' : 'Unpaid'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="<?php echo $sale['is_returned'] === '1' ? 'badge-block' : 'badge-pending'; ?>  px-2.5 py-0.5 inline-block">
                                                <?php echo $sale['is_returned'] === '1' ? 'Yes' : 'No'; ?>
                                            </div>
                                        </td>
                                        <td>


                                            <div class="flex justify-center">
                                                <div class="dropdown relative">
                                                    <button type="button"
                                                            class="text-white dropdown-toggle btn bg-primary-500 border-primary-500 hover:text-white hover:bg-primary-600 focus:text-white focus:bg-primary-600"
                                                            id="dropdownMenuheading-<?php echo $sale['sale_id']; ?>" onclick="toggleDropdown(<?php echo $sale['sale_id']; ?>)">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul id="dropdownMenu-<?php echo $sale['sale_id']; ?>" class="absolute z-50 py-2 mt-1 list-none bg-white rounded-md shadow-md dropdown-toggle-menu min-w-max hidden">
                                                        <li>
                                                            <a class="block px-4 py-1.5 text-base font-medium text-slate-600 dropdown-item"
                                                               href="sales-pdf.php?sale_id=<?php echo $sale['sale_id']; ?>">
                                                                <i class="fa-solid fa-file-pdf mr-1"></i> Save Pdf
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <?php if ($sale['is_returned'] == 1): ?>
                                                                <span class="block px-4 py-1.5 text-base font-medium text-gray-400 cursor-not-allowed dropdown-item">
                                                                    <i class="fa-solid fa-ban mr-1"></i> Returned
                                                                </span>
                                                            <?php else: ?>
                                                                <a href="sales-return.php?sale_id=<?php echo $sale['sale_id']; ?>" class="block px-4 py-1.5 text-base font-medium text-slate-600 dropdown-item">
                                                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Return
                                                                </a>
                                                            <?php endif; ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">No sales found</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Main Content End -->
</div>


<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden ease-in-out transition-all duration-500"></div>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>


<!--dataTables-->
<script type="module" src="assets/libs/datatables/jquery-3.7.0.js"></script>
<script type="module" src="assets/libs/datatables/data-tables.min.js"></script>
<script type="module" src="assets/libs/datatables/data-tables.tailwindcss.min.js"></script>

<script type="module" src="assets/js/datatable-util.js"></script>
<script type="module" src="assets/js/datatables.js"></script>

<script>
    function toggleDropdown(id) {
        var dropdownMenu = document.getElementById('dropdownMenu-' + id);
        dropdownMenu.classList.toggle('hidden');
    }
</script>


</body>
</html>

