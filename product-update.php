<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header('Location: login.php');
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
                                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAmCAYAAACoPemuAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDM1MiwgMjAyMC8wMS8zMC0xNTo1MDozOCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjEgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjdEM0Q3M0Q0OEE3MjExRUU5QzY1Qjg0RTZGNUJCMkJGIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjdEM0Q3M0Q1OEE3MjExRUU5QzY1Qjg0RTZGNUJCMkJGIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6N0QzRDczRDI4QTcyMTFFRTlDNjVCODRFNkY1QkIyQkYiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6N0QzRDczRDM4QTcyMTFFRTlDNjVCODRFNkY1QkIyQkYiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz70YlHmAAAK6UlEQVR42sxYaWxc1RX+7lvmzbzxrF7GdhyvWWwTBRxCQkIhENIqRUAoNGkISmhEQS3QFoWiCoQqukCrFkiQkChVEQUUCBUglgIikEBSlYpAQghktR0neJ2xZ9/fdntmxontYNC4vxjreGbedr97lu98ZxjnHN/Gl4Bv6UuaFu2e4W++SxbAZfE8KOJP2mThkkYGf4rjFYsjXW0Tf2QwpkWAfcOW9cyoyf/DGIPPAhjdOl18gh2urxxj04VS2Dvy9aDoem4Tt0GRfumiZSoMC00OG+baRRyM6jilAVVOjmqbAEWRcBJ4ZcjkN1TTMuLXABtuLxfYuwNfD8wmvgrLXMNhA3ImZLuFBVUepC0DMcGG6OkQ9KwJ+Cvh8jJ00vkBRd4f0flSlXNzOmDhTnd5OcY1fVqDZT2EZHoNz9jhPD4AW/9xdM2bBWMkgv6whdv5ADTvf7GnugdXDu5FsnsYB+jaQDZ/ocL4C2FyQnwaKz/5Gf+qiawBmeyved4Jpf8ktvY8gcYFLRjMAJd9+hqcuTjWnP43XZvFZdV5vNeexEvxN6EfOYQB5kNjXltLSfYdk4BY51jZwFyWNcU83ILDNDZwUxEwFMTj+36P/ouvQk/Kge1/uw7Lo8dxfqQHF6SOIqkxJMJp6PZq3HD5XLzU9xxCn3+KUU8VnJp+M0wL/BwrG1ijYU6xFsoMv8FXIWng6rd+g8vrZFihILa9di+ebrsGf1y4Ho+NvQUu2MGTEbhTw5DGTsPIirjhig7ctvNRDPeGYCnqSugERDOnWrnAJN2YYgLll+gLdGD3C9iY2I85rdWoDX+Ju7oewDM1m/Dd+EGcZxvFMFWkYeg4aatCEiqkINVkbT3ud4fgfO5hZKvVVqYZs1jeACNAZ6xsYE5hqrlUmzfRO1i9rO9jrLtyIf6srsLP/bdixeGnsPODH+Ch6AcYstWg3ojg6agXbXwdvNU3YnXTRvTERMy+ahk2x08Bbx4H97kDxIHgknDWyiZYfXLciRxFQfTHPvlYWUmeQlcrnjgCrDt0L570d8PT2oJ+exU4hTxvc2JJbgBbep/F7j0ZvONswuUXdmKgNoINi5J4/OBh4KLZlUV3WHzmzH+cO89+1riAGkOudQRUtPj9SGgS3sjuwIexo2j8pAZb18/GTfOBmC5gjMu4tMOH2PaXYY8F8GDLcWh9R2B5mlDTWAm0NdADbQFoFQVkMwe2Pnl0IqzcQJ+9rfJfTKTKzEPhGha01OKJYyaSJ1/DJycqccuqFVBCydINvlnYcSKE5w+cwsN3b8Ld8wkQDCCTJnLOocamVV0c60aGiZNWXFAesCexb9K3BF6Pjf3i1ZQXCtMhMAmG6MZidz+W1tfgx+fTwnZi+Ro/uEUkrNvx4O03I/Lbv6IyM0T9qQtIj0HSMoUcwc/y3Xc8kH51G0TvzIHFxFKLYKIETzq/6nT30VUwlkOy2yFTboylNGy8ZD42X3sRDFOE0R+EVBMAKnzkmTya2+fh7Ze3EnVkYYzFIbEMBAozDAn53t45SCc2oML+/Dfl2bTAcqnUBLBszlSipBXm1kGuaKGTURRIMp/VYIQshIdDSA2MwCGYcNXWQa1yQWrugOCuBafCYUa2eA8P1BHwefCPUAFYGRNW/GxDZ+UCQ6YErHBjXpLfX6Ian4pCqivu9tMiYWg5ApUepTYlIDYSxPDpbrB8Bn7irUB7OzwVXgqdDiYrFHp6ipZHWPUA1SquiKTDCGbe0AVWrPiiiioXWDW0s59F2k+nW9lk6sb+nhyzgQBk02mwVBoG8VFNUx0aWgKIZ3UoHg9Ufw1MykMzHSMhQo+XCyFM4ktQReYMVIdO34xUIiPL/wddfN+1ckLlUFknK+xfQKvfHswENyMXQS6bIbWRJ89UYE9fEH3RNBbWe1FJrUs1BXjcHlQQaIOKQTA12LQo+hU7PU3du7r5pjfbrCQywoSfdpcL7F3WOFlqlJLAyu16n1dtTkkSRDODGAHzcRsO9A5h665j6Kyqgc/txGh4FHde04VbVy9GlFJJoXBDtuEtexvlRer9Y+5WHBMJJDdn7jGFZ6YeMBlMwXzviFarP6nPlrcoR7EnpqMnFMPGpS24cVknokTKukSRToTRHKhAKJ2HSQ3b58rgdaKDt0ViYSPxNisQq5E8Z0Vveb2SZO1UI+kj28Qg7MI79xz2YVdQxYo2B7VpDeFYAjYhR/I6hnnKGBY3O+Fx2iCZOdQ1MAydyOK2j4hGJIEamfVRQcOea2U38ZxdmjBFRM5GJUA5o7LcDm5347q9DfjToTr4fAE0qAyxtI6ILmEsx9AfycMtcDgkFc/stLDoHxUIumbTbtP/BCuElfKCZB1sbMKmd85XEV/w6N5SnOlUhsq61yFCo3yBQSxpM05h+856fBGOYnFL6pam0dlbqj5HJJ6CrDoxv96HR3pn4Q+fqaSrTwlYen4Av1tnICe0wtD7ZYdEateCNckl8TVt5eXYwcORST4VEMkaeOHq2XhIVfShY7HbUT/r7wzKDh7Dh0dm+Z8dSYWl8InDaOjsgLe1FkNHc0CP4z7HvJZA9sK59yMt3Ofyif2/UmX89PkR+NMWsrTZswy7plyCnVSQhVz1j+VwR7MbP+zy40W79vqBqrnLv/fYiQ3tX7x45+igO5d3sYpqoRKBqgDSh+rxyEBSfyqRvqNvzpLdL9208FLVazuytt2DmhD10hHiSEqLwugHPsOqtISJuDPKB6ZInuGU/pd8Trvuljku16Y2iWUdVyjarhr4jwxCiEvwOSthT1WCdQegLqqV+Vr34uaLKhffs6TyLmjEaAJyyYy526VIW4gcTxdza6bAijcUrHCzjrnoz3wkpPI+jZJ6WCMXmlQUyzuRu3IBBGJ81RCLel+y0axZIYO5ZWpFMiRq0nI0byu43pIsu8z49UiY10MVV1Al7YU+wybOVDpc6mU1rC+1D6MprxzJgheqqdDfJBH2LCcjnUWVxoo/GXDiOpoRMhYkGoQlkXol5Scf74dW4Y16LAK0sVG2hybkxXBgP8wZ/KjCyFPMUVAGxjZkc16E4kAwWdTnAi0mUA8UZanoIZlYXabeKNECosEhckZ7YpOixMa7B3l1kMRBlgRjA51N6I8XJyZjBsOIRTunUXQ+svkbMUayRcvBHBqEqZW8UAQniIVZAKJUMKkEViy8E+cV5A5j5+SFHUJ3CNh7qtBa6Kt1MRLW6ulFz9cAM+mPm/p6RAlUjNzvo5bxWR8wHKFCkMeBCUWQrBBKAnH2GJuQMyVwBJLSQqDyth0epDAmgBDRiYMXFO0GPhOPyRItbvBrEckV+yTsFC4iRRtdziQRZ9ZmZ6LEJnwzhWv4ZB1g0QxJioK8jDD1YrEwjJhX8YRmL/+3C0nqQFpbRHlQTHTEY+Dz6yDUk3zWjG+kPT6ptPmZb1bhkwira1bpgkS+qP8pOSsFSV5ZPrDR1KUYpFMRFz2IlCe1I62dZkcKI0wTZ7oYH//HJ0Ep/n42bmcuKJ23YHaQAnbQM+P0nBg5Ku0AxrCibLoYmDeUkdJRKIhB5TQFqUHkmhcVFSgnpVHYDqd5k511ExsHVYJRUCSlSJaOlSKtw2h0wqxPk+TvAc9S25snwJiTz6hYVh6w7ALFnfFEYTZ8nlJ1kUdCKbia1sIV0WkOMSkoVLNkAhvnNWEcIZEV4S0OP8xiRSosnadNJKikKlUEb+0Hj/ZDaWmF3OR1CS67YzoM/xNgABLCEjTKWbavAAAAAElFTkSuQmCC" alt="" class="w-12 h-12 rounded">
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
                            <a href="product-create.php" class="flex items-center flex-row w-full px-3 py-2.5 ">
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
