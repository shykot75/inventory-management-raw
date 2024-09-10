<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';  // Include the database connection

// Fetch all sale returns from the database
function getSalesReturns($connection) {
    $query = "SELECT sr.sale_return_id, sr.return_date, sr.return_quantity, sr.return_amount, 
                     sr.payment_status, prod.product_name, prod.product_price, prod.quantity as current_stock
              FROM sale_returns sr
              JOIN products prod ON sr.product_id = prod.product_id
              WHERE sr.deleted_at IS NULL
              ORDER BY sr.return_date DESC";

    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    return [];
}

$saleReturns = getSalesReturns($connection);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sales Return List | IMS</title>
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
                    <h6 class="mb-4 text-15">Sales Return Lists</h6>
                    <table id="borderedTable" class="bordered group" style="width:100%">
                        <thead>
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Return Qty.</th>
                            <th>Grand Total</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($saleReturns) > 0): ?>
                            <?php foreach ($saleReturns as $index => $item): ?>
                                <tr class="text-sm text-center">
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($item['return_date']) ?? '--'; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($item['product_name']) ?? '--'; ?> <br>
                                        <small>Price: <?php echo htmlspecialchars($item['product_price']) ?? '--'; ?> Tk</small>
                                        <small>CS: <?php echo htmlspecialchars($item['current_stock']) ?? '--'; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['return_quantity']) ?? '--'; ?></td>
                                    <td><?php echo htmlspecialchars($item['return_amount']) ?? '--'; ?> Tk</td>
                                    <td>
                                        <div class="<?php echo $item['payment_status'] == 'paid' ? 'badge-active' : 'badge-inactive'; ?> px-2.5 py-0.5 inline-block">
                                            <?php echo $item['payment_status'] == 'paid' ? 'Paid' : 'Not Paid'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex justify-center">
                                            <div class="dropdown relative">
                                                <button type="button"
                                                        class="text-white dropdown-toggle btn bg-primary-500 border-primary-500 hover:text-white hover:bg-primary-600 focus:text-white focus:bg-primary-600"
                                                        id="dropdownMenuheading-<?php echo $item['sale_return_id']; ?>" onclick="toggleDropdown(<?php echo $item['sale_return_id']; ?>)">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul id="dropdownMenu-<?php echo $item['sale_return_id']; ?>" class="absolute z-50 py-2 mt-1 list-none bg-white rounded-md shadow-md dropdown-toggle-menu min-w-max hidden">
                                                    <li>
                                                        <a class="block px-4 py-1.5 text-base font-medium text-slate-600 dropdown-item"
                                                           href="sales-return-pdf.php?sale_return_id=<?php echo $item['sale_return_id']; ?>">
                                                            <i class="fa-solid fa-file-pdf mr-1"></i> Save Pdf
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No Sales Returns Found</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
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

