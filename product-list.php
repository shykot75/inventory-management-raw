<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit();
}

include 'config.php'; // Include the database connection

// Fetch categories from the database
function getCategories($connection) {
    $query = "SELECT category_id, category_name FROM categories";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}

// Fetch products with optional filters
function getProducts($connection, $filters) {
    $query = "SELECT p.*, c.category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.category_id 
              WHERE p.deleted_at IS NULL"; // Ensure only non-deleted products are retrieved

    // Apply filters
    if (!empty($filters['category_id'])) {
        $category_id = mysqli_real_escape_string($connection, $filters['category_id']);
        $query .= " AND p.category_id = '$category_id'";
    }

    // Sorting by price or quantity, if provided
    if (!empty($filters['product_price'])) {
        $order_by = $filters['product_price'] === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY p.product_price $order_by";
    } elseif (!empty($filters['quantity_order'])) {
        $order_by = $filters['quantity_order'] === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY p.quantity $order_by";
    } else {
        // Default ordering by created_at in descending order
        $query .= " ORDER BY p.created_at DESC";
    }

    // Execute the query
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}


// Fetch categories and products
$categories = getCategories($connection);
$filters = [
    'category_id' => $_GET['category_id'] ?? null,
    'product_price' => $_GET['product_price'] ?? null,
    'quantity_order' => $_GET['quantity_order'] ?? null,
];
$products = getProducts($connection, $filters);

// Handle product deletion
if (isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];
    $delete_query = "DELETE FROM products WHERE product_id = ?";
    $stmt = $connection->prepare($delete_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    header("Location: product-list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-mode="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Product List | IMS</title>
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
<nav class="sticky-navbar bg-light text-light dark:bg-dark dark:text-dark shadow-md shadow-slate-200 dark:shadow-slate-900">
    <div class="mx-auto max-w-8xl px-2">
        <div class="flex flex-col lg:flex-row h-32 lg:h-16 items-center justify-between lg:justify-start">
            <div class="flex justify-between items-center gap-x-4 w-full lg:w-fit border-b lg:border-0">
                <div class="h-16 flex justify-center items-center w-52 sm:grow md:grow-0">
                    <p class="flex items-center w-full px-3 text-primary">Inventory Management System</p>
                </div>
                <div class="flex items-center ml-6">
                    <div id="toggle-sidebar"
                         class="toggle-sidebar relative inline-flex items-center justify-center p-2 bg-light text-light dark:bg-dark dark:text-dark  hover:text-primary-600 dark:hover:bg-gray-700 dark:hover:text-white focus:outline-none"
                         aria-controls="mobile-menu" aria-expanded="false">
                        <span class="absolute -inset-0.5"></span>
                        <span class="sr-only">Open main menu</span>
                        <i class="fa-solid fa-bars"></i>
                    </div>
                </div>
            </div>

            <div class="grow-0 lg:grow w-full lg:w-fit py-4 lg:py-0 px-2">
                <div class="flex items-center justify-between lg:justify-end ml-0 lg:ml-6 gap-x-4">

                    <!-- Profile dropdown -->
                    <div class="relative ml-3">
                        <div>
                            <button type="button"
                                    class="relative flex max-w-xs items-center rounded-full bg-light dark:bg-dark text-sm focus:outline-none"
                                    id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="absolute -inset-1.5"></span>
                                <span class="sr-only">Open user menu</span>
                                <img class="h-8 w-8 rounded-full" src="assets/images/profile.png" alt="">
                            </button>
                        </div>

                        <div id="profile-dropdown"
                             class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden transition ease-out duration-200 transform opacity-0 scale-95"
                             role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                            <a href="#!" class="flex gap-3 mb-3 border-b">
                                <div class="relative inline-block shrink-0">
                                    <div class="rounded bg-slate-100 dark:bg-zinc-500">
                                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAmCAYAAACoPemuAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDM1MiwgMjAyMC8wMS8zMC0xNTo1MDozOCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjEgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjdEM0Q3M0Q0OEE3MjExRUU5QzY1Qjg0RTZGNUJCMkJGIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjdEM0Q3M0Q1OEE3MjExRUU5QzY1Qjg0RTZGNUJCMkJGIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6N0QzRDczRDI4QTcyMTFFRTlDNjVCODRFNkY1QkIyQkYiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6N0QzRDczRDM4QTcyMTFFRTlDNjVCODRFNkY1QkIyQkYiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz70YlHmAAAK6UlEQVR42sxYaWxc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" alt="" class="w-12 h-12 rounded">
                                    </div>
                                    <span class="-top-1 ltr:-right-1 rtl:-left-1 absolute w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full dark:border-zinc-600"></span>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-15"><?php echo $_SESSION['user_name']; ?></h6>
                                    <p class="text-slate-500 dark:text-zinc-300 uppercase text-sm capitalize"><?php echo $_SESSION['user_role']; ?></p>
                                </div>
                            </a>

                            <a href="logout.php"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Log Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- Navbar End -->

<div class="flex">
    <!-- Sidebar Start -->
    <aside id="sidebar" class="sidebar border-r dark:border-gray-800 dark:shadow-lg bg-light text-light dark:bg-dark dark:text-dark">
        <!-- Component Start -->
        <div class="app-menu flex flex-col w-full h-full">
            <ul class="space-y-2 w-full px-2 mt-2">

                <li class="relative menu-item">
                    <a href="dashboard.php" class="parent-item active flex items-center w-full px-3 py-3">
                        <i class="fa-solid fa-house text-sm "></i>
                        <span class="menu-title ml-4 text-sm font-medium">Dashboard</span>
                    </a>
                </li>

                <!-- Profile Dropdown Start -->
                <li class="relative menu-item">
                    <div class="parent-item flex items-center flex-row w-full px-3 py-3 cursor-pointer">
                        <i class="fa-solid fa-cart-shopping text-sm"></i>
                        <span class="menu-title ml-4 text-sm font-medium grow">Product</span>
                        <i class="fa-solid fa-angle-down arrow-icon"></i>
                    </div>
                    <ul class="dropdown-menu bg-white text-light dark:bg-dark dark:text-dark ml-2">
                        <li class="dropdown-item">
                            <a href="product-list.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                                <i class="fa-solid fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium">Product List</span>
                            </a>
                        </li>
                        <li class="dropdown-item">
                            <a href="create-product.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
                                <i class="fa-solid fa-chevron-right"></i>
                                <span class="ml-4 text-sm font-medium">Add Product</span>
                            </a>
                        </li>

                    </ul>
                </li>
                <!-- Profile Dropdown End -->

            </ul>
        </div>
    </aside>
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
                    <h6 class="mb-4 text-15">Product Filter</h6>
                    <form action="product-list.php" method="GET">
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <div>
                                <label for="category_id" class="form-input-label text-base font-medium">Category</label>
                                <select name="category_id" class="form-input form-select" id="category_id">
                                    <option selected disabled>--select category--</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo isset($filters['category_id']) && $filters['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="product_price" class="form-input-label text-base font-medium">Sort by Price</label>
                                <select name="product_price" class="form-input form-select" id="product_price">
                                    <option selected disabled>--sort by price--</option>
                                    <option value="ASC" <?php echo isset($filters['product_price']) && $filters['product_price'] == 'ASC' ? 'selected' : ''; ?>>Low to High</option>
                                    <option value="DESC" <?php echo isset($filters['product_price']) && $filters['product_price'] == 'DESC' ? 'selected' : ''; ?>>High to Low</option>
                                </select>
                            </div>

                            <div>
                                <label for="quantity_order" class="form-input-label text-base font-medium">Sort by Quantity</label>
                                <select name="quantity_order" class="form-input form-select" id="quantity_order">
                                    <option selected disabled>--sort by quantity--</option>
                                    <option value="ASC" <?php echo isset($filters['quantity_order']) && $filters['quantity_order'] == 'ASC' ? 'selected' : ''; ?>>Low to High</option>
                                    <option value="DESC" <?php echo isset($filters['quantity_order']) && $filters['quantity_order'] == 'DESC' ? 'selected' : ''; ?>>High to Low</option>
                                </select>
                            </div>
                        </div>

                        <div class="w-full flex mt-5 justify-start gap-3">
                            <a href="product-list.php" class="btn text-white bg-red-500 border-red-600 hover:text-white hover:bg-red-800">
                                <i class="fa-solid fa-refresh mr-1"></i> Reset
                            </a>
                            <button type="submit" class="btn text-white bg-primary-500 border-primary-600 hover:bg-primary-800">
                                <i class="fa-solid fa-magnifying-glass mr-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <h6 class="mb-4 text-15">Product Lists</h6>
                    <table id="borderedTable" class="bordered group" style="width:100%">
                        <thead>
                        <tr>
                            <th>SL</th>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $index => $product): ?>
                                <tr class="text-sm text-center">
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="product-image" class="w-12 h-12 items-center">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? '--'); ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['product_price']); ?> Tk</td>
                                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                    <td>
                                        <div class="<?php echo $product['status'] == 1 ? 'badge-active' : 'badge-inactive'; ?> px-2.5 py-0.5 inline-block">
                                            <?php echo $product['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="edit-product.php?id=<?php echo $product['product_id']; ?>" class="block px-4 py-1.5 text-base font-medium text-slate-600">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                        </a>
                                        <form action="product-list.php" method="POST">
                                            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                            <button type="submit" name="delete" class=" px-4 py-1.5 text-base font-medium text-red-500">
                                                <i class="fa-solid fa-trash-can mr-1"></i> Delete
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No products found</td>
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
    function confirmDelete(productId) {
        if (confirm("Are you sure you want to delete this item?")) {
            document.getElementById('delete-form-' + productId).submit();
        }
    }
</script>

</body>
</html>

