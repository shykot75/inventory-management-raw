<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
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

    // Delete query using traditional mysqli_query
    $delete_query = "DELETE FROM products WHERE product_id = '$product_id'";

    if (mysqli_query($connection, $delete_query)) {
        $_SESSION['success'] = "Product deleted successfully";
        // Redirect to the product list page after successful deletion
        header("Location: product-list.php");
        exit();
    } else {
        $_SESSION['error'] = "Error deleting product: " . mysqli_error($connection);
    }
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
                                        <a href="product-update.php?product_id=<?php echo $product['product_id']; ?>" class="block px-4 py-1.5 text-base font-medium text-slate-600">
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

