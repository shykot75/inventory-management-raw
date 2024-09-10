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

include 'config.php';  // Include the database connection

// Check if the 'id' parameter is passed in the URL
if (!isset($_GET['product_id']) || empty($_GET['product_id'])) {
    echo "Product ID is missing.";
    exit();
}

$product_id = $_GET['product_id'];

// Traditional way to fetch product details
function getProduct($connection, $product_id) {
    $query = "SELECT * FROM products WHERE product_id = '$product_id' AND deleted_at IS NULL";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result); // Fetch product details as an associative array
    }
    return null; // Return null if no product found
}

$product = getProduct($connection, $product_id);

if (!$product) {
    die('Product not found.');
}

// Fetch categories traditionally
function getCategories($connection) {
    $query = "SELECT category_id, category_name FROM categories";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}

$categories = getCategories($connection);

// Handle the form submission for update
if (isset($_POST['submit'])) {
    // Capture form data
    $product_name = $_POST['product_name'];
    $sku = $_POST['sku'];
    $category_id = $_POST['category_id'];
    $product_price = $_POST['product_price'];
    $quantity = $_POST['quantity'];
    $status = $_POST['status'] ?? 1;
    $product_description = $_POST['product_description'] ?? null;
    $product_image = $_FILES['product_image'] ?? null;
    $old_image = $product['product_image'];  // Old image from the database

    // Initialize an errors array
    $errors = [];

    // Validation rules
    if (empty($product_name)) {
        $errors[] = "Product Name is required";
    }

    if (empty($sku)) {
        $errors[] = "SKU is required";
    } else {
        // Check if SKU is unique except for the current product
        $sku_query = "SELECT sku FROM products WHERE sku = '$sku' AND product_id != '$product_id'";
        $sku_result = mysqli_query($connection, $sku_query);
        if (mysqli_num_rows($sku_result) > 0) {
            $errors[] = "SKU must be unique";
        }
    }

    if (empty($category_id) || !is_numeric($category_id)) {
        $errors[] = "Select a valid Category";
    }

    if (empty($product_price) || !is_numeric($product_price)) {
        $errors[] = "Product Price is required and should be a valid number";
    }

    if (empty($quantity) || !is_numeric($quantity)) {
        $errors[] = "Quantity is required and should be a valid number";
    }

    // Image upload handling
    if (!empty($product_image['name'])) {
        $allowed_extensions = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp'];
        $file_extension = strtolower(pathinfo($product_image['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Product image must be one of the following formats: jpeg, png, jpg, gif, svg, webp";
        } elseif ($product_image['size'] > 2048 * 1024) {
            $errors[] = "Product image size must not exceed 2MB";
        } else {
            // Upload the new image and remove the old one
            $target_dir = "uploads/products/";
            $new_image_name = uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_image_name;

            if (move_uploaded_file($product_image['tmp_name'], $target_file)) {
                $image_path = $target_file;
                if (file_exists($old_image)) {
                    unlink($old_image);  // Remove old image from server
                }
            } else {
                $errors[] = "Error uploading product image";
            }
        }
    } else {
        $image_path = $old_image;  // Retain the old image if no new image is uploaded
    }

    // If no errors, update the product in the database
    if (empty($errors)) {
        $query = "UPDATE products 
                  SET product_name = '$product_name', sku = '$sku', category_id = '$category_id', 
                      product_price = '$product_price', quantity = '$quantity', 
                      status = '$status', product_description = '$product_description', product_image = '$image_path' 
                  WHERE product_id = '$product_id'";

        if (mysqli_query($connection, $query)) {
            $_SESSION['success'] = "Product updated successfully";
        } else {
            $_SESSION['error'] = "Error updating product in the database";
        }

        // Redirect after form submission
        header("Location: product-list.php");
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
    <title>Admin | Edit Product</title>
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

    <!-- Main Content Start -->
    <main class="w-full min-h-full pt-4 px-4 pb-12 text-light dark:bg-black dark:text-dark">

        <?php
        // Replace this with your PHP login alert logic
        include('components/login-alert-message.php');
        ?>

        <div class="main-body">
            <div class="card">
                <div class="card-header rounded-t-md">
                    <h6 class="text-lg card-title">Edit Product</h6>
                </div>
                <div class="card-body">
                    <form action="product-update.php?product_id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <!-- Product Name -->
                            <div>
                                <label for="product_name" class="form-input-label text-base font-medium required">Product Name</label>
                                <input type="text" name="product_name" id="product_name" class="form-input" placeholder="Enter Product Name"
                                       value="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </div>

                            <!-- SKU -->
                            <div>
                                <label for="sku" class="form-input-label text-base font-medium required">SKU</label>
                                <input type="text" name="sku" id="sku" class="form-input" placeholder="#0001"
                                       value="<?php echo htmlspecialchars($product['sku']); ?>">
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category_id" class="form-input-label text-base font-medium required">Category</label>
                                <select name="category_id" class="form-input form-select" id="category_id">
                                    <option selected disabled>--select category--</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo ($category['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Product Price, Quantity, and Status -->
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <!-- Product Price -->
                            <div>
                                <label for="product_price" class="form-input-label text-base font-medium required">Price</label>
                                <input type="number" name="product_price" id="product_price" class="form-input" placeholder="Enter Price"
                                       min="1" oninput="validity.valid||(value='');" onwheel="this.blur()"
                                       value="<?php echo htmlspecialchars($product['product_price']); ?>">
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label for="quantity" class="form-input-label text-base font-medium required">Quantity</label>
                                <input type="number" name="quantity" id="quantity" class="form-input" placeholder="Enter Quantity"
                                       min="1" oninput="validity.valid||(value='');" onwheel="this.blur()"
                                       value="<?php echo htmlspecialchars($product['quantity']); ?>">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="form-input-label text-base font-medium required">Status</label>
                                <div class="flex gap-x-2 mt-1">
                                    <div class="flex items-center py-1 gap-x-4 mr-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status" class="border rounded-full appearance-none size-4 bg-slate-100 border-slate-200 checked:bg-primary-500 checked:border-primary-500"
                                                   value="1" <?php echo ($product['status'] == 1) ? 'checked' : ''; ?>>
                                            <span class="ml-2 text-base">Active</span>
                                        </label>
                                    </div>
                                    <div class="flex items-center py-1 gap-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="status" class="border rounded-full appearance-none size-4 bg-slate-100 border-slate-200 checked:bg-primary-500"
                                                   value="0" <?php echo ($product['status'] == 0) ? 'checked' : ''; ?>>
                                            <span class="ml-2 text-base">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Image and Description -->
                        <div class="grid xl:grid-cols-2 gap-x-4 my-2">
                            <!-- Product Image -->
                            <div>
                                <label for="product_image" class="form-input-label text-base font-medium">Product Image</label>
                                <div class="w-full">
                                    <div class="file-input-container relative flex gap-2 items-center justify-between border border-slate-300 rounded-lg shadow-md p-4">
                                        <div class="flex flex-col">
                                            <label for="fileInput1" class="file-label relative z-10 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-500 border border-transparent rounded-md cursor-pointer hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 12c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm8-3v7c0 1.105-.895 2-2 2H4c-1.105 0-2-.895-2-2V9h2v7h12V9h2zm-2-3H4V4c0-1.105.895-2 2-2h4v3h4V2h2c1.105 0 2 .895 2 2v2z"/></svg>
                                                <span>Choose File</span>
                                            </label>
                                            <span class="ml-4 mt-1 text-gray-500 file-name text-wrap">No file chosen</span>
                                        </div>
                                        <div class="image-preview w-20 min-h-20 border border-dashed">
                                            <?php if (!empty($product['product_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="Image Preview" class="rounded-md shadow-md">
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" name="product_image" id="fileInput1" class="hidden form-file-input" accept="image/*"/>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Description -->
                            <div>
                                <label for="product_description" class="form-input-label text-base font-medium">Product Description</label>
                                <textarea name="product_description" id="product_description" class="form-input" rows="4"
                                          placeholder="Write Product Description"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="w-full">
                            <div class="flex w-full mt-5 justify-end">
                                <div class="flex gap-3">
                                    <a href="product-list.php" class="btn text-white bg-red-500 border-red-600 hover:text-white hover:bg-red-800">
                                        <i class="fa-solid fa-xmark mr-1"></i> Cancel
                                    </a>
                                    <button type="submit" name="submit" class="btn text-white bg-primary-500 border-primary-600 hover:text-white hover:bg-primary-800">
                                        <i class="fa-regular fa-square-check mr-1"></i> Update
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
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
</body>
</html>
