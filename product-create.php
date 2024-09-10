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

// Fetch categories from the database
function getCategories($connection) {
    $query = "SELECT category_id, category_name FROM categories";
    $result = mysqli_query($connection, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    return [];
}

$categories = getCategories($connection);  // Fetch categories

// Check if form is submitted
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

    // Initialize an errors array
    $errors = [];

    // Validation rules (similar to your ProductRequest)
    if (empty($product_name)) {
        $errors[] = "Product Name is required";
    }

    if (empty($sku)) {
        $errors[] = "SKU is required";
    } else {
        // Check if SKU is unique (assuming a products table)
        $sku_query = "SELECT sku FROM products WHERE sku = '$sku'";
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

    if (!empty($product_image['name'])) {
        $allowed_extensions = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp'];
        $file_extension = strtolower(pathinfo($product_image['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Product image must be one of the following formats: jpeg, png, jpg, gif, svg, webp";
        } elseif ($product_image['size'] > 2048 * 1024) {
            $errors[] = "Product image size must not exceed 2MB";
        }
    }

    if (empty($status)) {
        $errors[] = "Product status is required";
    }

    // Handle image upload
    if (!empty($product_image['name']) && empty($errors)) {
        $target_dir = "uploads/products/"; // Updated path to reflect relative directory structure
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
        }

        $image_name = time() . '_' . basename($product_image['name']); // To avoid overwriting
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($product_image['tmp_name'], $target_file)) {
            $image_path = "uploads/products/" . $image_name; // Store the relative path in the database
        } else {
            $errors[] = "Error uploading product image";
        }
    }


    // If no errors, proceed to insert into database
    if (empty($errors)) {
        // Traditional approach using `mysqli_query()`
        $query = "INSERT INTO products (product_name, sku, category_id, product_price, quantity, status, product_description, product_image) 
                  VALUES ('$product_name', '$sku', $category_id, $product_price, $quantity, $status, '$product_description', '$image_path')";

        $insert_result = mysqli_query($connection, $query);

        if ($insert_result) {
            $_SESSION['success'] = "Product created successfully";
        } else {
            $_SESSION['error'] = "Error inserting product into the database: " . mysqli_error($connection);
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
    <title>Product Create | IMS</title>
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
                <div class="card-header rounded-t-md">
                    <div class="flex justify-between items-center gap-x-4">
                        <h6 class="text-lg card-title">Add New Product</h6>
                    </div>
                </div>
                <div class="card-body">
                    <form action="product-create.php" method="POST" enctype="multipart/form-data">
                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <div>
                                <label for="product_name" class="form-input-label text-base font-medium required">Product Name</label>
                                <input type="text" name="product_name" id="product_name" class="form-input" placeholder="Enter Product Name"
                                       value="<?php echo isset($_SESSION['old']['product_name']) ? htmlspecialchars($_SESSION['old']['product_name']) : ''; ?>">
                            </div>
                            <div>
                                <label for="sku" class="form-input-label text-base font-medium required">SKU</label>
                                <input type="text" name="sku" id="sku" class="form-input" placeholder="#0001"
                                       value="<?php echo isset($_SESSION['old']['sku']) ? htmlspecialchars($_SESSION['old']['sku']) : ''; ?>">
                            </div>
                            <div>
                                <label for="category_id" class="form-input-label text-base font-medium required">Category</label>
                                <select name="category_id" class="form-input form-select" id="category_id">
                                    <option selected disabled>--select category--</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"
                                            <?php echo isset($_SESSION['old']['category_id']) && $_SESSION['old']['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid xl:grid-cols-3 gap-x-4 my-2">
                            <div>
                                <label for="product_price" class="form-input-label text-base font-medium required">Price</label>
                                <input type="number" name="product_price" id="product_price" class="form-input" placeholder="Enter Price"
                                       min="1" oninput="validity.valid||(value='');" onwheel="this.blur()"
                                       value="<?php echo isset($_SESSION['old']['product_price']) ? htmlspecialchars($_SESSION['old']['product_price']) : ''; ?>">
                            </div>
                            <div>
                                <label for="quantity" class="form-input-label text-base font-medium required">Quantity</label>
                                <input type="number" name="quantity" id="quantity" class="form-input" placeholder="Enter Quantity"
                                       min="1" oninput="validity.valid||(value='');" onwheel="this.blur()"
                                       value="<?php echo isset($_SESSION['old']['quantity']) ? htmlspecialchars($_SESSION['old']['quantity']) : ''; ?>">
                            </div>
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

                        <div class="grid xl:grid-cols-2 gap-x-4 my-2">
                            <div>
                                <label for="product_image" class="form-input-label text-base font-medium">Product Image</label>
                                <div class="w-full">
                                    <div class="file-input-container relative flex gap-2 items-center justify-between border border-slate-300 dark:border-slate-600 rounded-lg shadow-md p-4">
                                        <div class="flex flex-col">
                                            <label for="fileInput1" class="file-label relative z-10 inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-500 border border-transparent rounded-md cursor-pointer hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 12c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm8-3v7c0 1.105-.895 2-2 2H4c-1.105 0-2-.895-2-2V9h2v7h12V9h2zm-2-3H4V4c0-1.105.895-2 2-2h4v3h4V2h2c1.105 0 2 .895 2 2v2z"/></svg>
                                                <span>Choose File</span>
                                            </label>
                                            <span class="ml-4 mt-1 text-gray-500 file-name text-wrap dark:text-dark">No file chosen</span>
                                        </div>
                                        <div class="image-preview w-20 min-h-20 border border-dashed">
                                            <img src="" alt="Image Preview" class="hidden rounded-md shadow-md">
                                        </div>
                                        <input type="file" name="product_image" id="fileInput1" class="hidden form-file-input" accept="image/*"/>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="product_description" class="form-input-label text-base font-medium">Product Description</label>
                                <textarea name="product_description" id="product_description" class="form-input" rows="4"
                                          placeholder="Write Product Description"><?php echo isset($_SESSION['old']['product_description']) ? htmlspecialchars($_SESSION['old']['product_description']) : ''; ?></textarea>
                            </div>
                        </div>

                        <div class="w-full">
                            <div class="flex w-full mt-5 justify-end">
                                <div class="flex gap-3">
                                    <a href="" class="btn text-white bg-red-500 border-red-600 hover:text-white hover:bg-red-800">
                                        <i class="fa-solid fa-xmark mr-1"></i> Cancel
                                    </a>
                                    <button type="submit" name="submit" class="btn text-white bg-primary-500 border-primary-600 hover:text-white hover:bg-primary-800">
                                        <i class="fa-regular fa-square-check mr-1"></i> Save
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

<?php unset($_SESSION['old']); unset($_SESSION['errors']); // Clear old input values ?>

<!-- Overlay -->
<div id="overlay" class="fixed inset-0 bg-black opacity-50 hidden ease-in-out transition-all duration-500"></div>

<script type="module" src="assets/js/bcs-util.js"></script>
<script type="module" src="assets/js/main.js"></script>
</body>
</html>
