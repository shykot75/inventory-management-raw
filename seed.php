<?php
include 'config.php';  // Include the database connection

// Seed Admins
$admins = [
    [
        'name' => 'Super Admin',
        'email' => 'super@admin.com',
        'phone' => '01620128405',
        'password' => password_hash('12345678', PASSWORD_BCRYPT),
        'status' => 1,
        'role' => 'admin'
    ],
    [
        'name' => 'Shykot Hasan',
        'email' => 'shykot@gmail.com',
        'phone' => '01620128401',
        'password' => password_hash('12345678', PASSWORD_BCRYPT),
        'status' => 1,
        'role' => 'user'
    ]
];

foreach ($admins as $admin) {
    $name = $admin['name'];
    $email = $admin['email'];
    $phone = $admin['phone'];
    $password = $admin['password'];
    $status = $admin['status'];
    $role = $admin['role'];

    $insert_admin_query = "INSERT INTO users (name, email, phone, password, status, role) 
                           VALUES ('$name', '$email', '$phone', '$password', $status, '$role')";
    if (mysqli_query($connection, $insert_admin_query)) {
        echo "Admin '$name' inserted successfully.<br>";
    } else {
        echo "Error inserting admin '$name': " . mysqli_error($connection) . "<br>";
    }
}

// Seed Categories
$categories = [
    ['category_name' => 'Electronics', 'category_description' => 'Devices and gadgets'],
    ['category_name' => 'Beauty', 'category_description' => 'Various types of beauty products'],
    ['category_name' => 'Clothing', 'category_description' => 'Men and Women clothing'],
    ['category_name' => 'Home Appliances', 'category_description' => 'Household appliances'],
    ['category_name' => 'Sports', 'category_description' => 'Sports and fitness equipment']
];

foreach ($categories as $category) {
    $name = $category['category_name'];
    $description = $category['category_description'];

    $insert_category_query = "INSERT INTO categories (category_name, category_description) 
                              VALUES ('$name', '$description')";
    if (mysqli_query($connection, $insert_category_query)) {
        echo "Category '$name' inserted successfully.<br>";
    } else {
        echo "Error inserting category '$name': " . mysqli_error($connection) . "<br>";
    }
}

// Seed Customers
$customers = [
    ['customer_name' => 'Customer One', 'customer_phone' => '01829164241'],
    ['customer_name' => 'Customer Two', 'customer_phone' => '01829164241'],
    ['customer_name' => 'Customer Three', 'customer_phone' => '01829164241'],
    ['customer_name' => 'Customer Four', 'customer_phone' => '01829164241']
];

foreach ($customers as $customer) {
    $name = $customer['customer_name'];
    $phone = $customer['customer_phone'];

    $insert_customer_query = "INSERT INTO customers (customer_name, customer_phone) 
                              VALUES ('$name', '$phone')";
    if (mysqli_query($connection, $insert_customer_query)) {
        echo "Customer '$name' inserted successfully.<br>";
    } else {
        echo "Error inserting customer '$name': " . mysqli_error($connection) . "<br>";
    }
}

// Seed Suppliers
$suppliers = [
    ['supplier_name' => 'Supplier One', 'supplier_phone' => '01829164241'],
    ['supplier_name' => 'Supplier Two', 'supplier_phone' => '01829164241'],
    ['supplier_name' => 'Supplier Three', 'supplier_phone' => '01829164241'],
    ['supplier_name' => 'Supplier Four', 'supplier_phone' => '01829164241']
];

foreach ($suppliers as $supplier) {
    $name = $supplier['supplier_name'];
    $phone = $supplier['supplier_phone'];

    $insert_supplier_query = "INSERT INTO suppliers (supplier_name, supplier_phone) 
                              VALUES ('$name', '$phone')";
    if (mysqli_query($connection, $insert_supplier_query)) {
        echo "Supplier '$name' inserted successfully.<br>";
    } else {
        echo "Error inserting supplier '$name': " . mysqli_error($connection) . "<br>";
    }
}

// Close the database connection
mysqli_close($connection);
?>
