<?php
include 'config.php';  // Include the database connection

// Create Users Table
$sql_create_users_table = "
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status TINYINT(1) DEFAULT 1,
    role VARCHAR(50) DEFAULT 'user',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
)";

// Create Categories Table
$sql_create_categories_table = "
CREATE TABLE IF NOT EXISTS categories (
    category_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
)";

// Create Products Table
$sql_create_products_table = "
CREATE TABLE IF NOT EXISTS products (
    product_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    category_id INT(11) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    sku VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT(11) DEFAULT 10,
    product_image TEXT DEFAULT NULL,
    product_description TEXT DEFAULT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
)";


// Create Suppliers Table
$sql_create_suppliers_table = "
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(255) NOT NULL,
    supplier_phone VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
)";

// Create Customers Table
$sql_create_customers_table = "
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
)";


// Create Purchases Table
$sql_create_purchases_table = "
CREATE TABLE IF NOT EXISTS purchases (
    purchase_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    supplier_id INT(11) NOT NULL,
    purchase_quantity INT(11) NOT NULL,
    purchase_price INT(11) NULL,
    purchase_date DATE NOT NULL,
    purchase_total DECIMAL(10,2) NOT NULL,
    payment_type VARCHAR(50) NULL,
    payment_status VARCHAR(50) NULL,
    is_returned TINYINT(1) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE CASCADE
)";

// Create Purchase Returns Table
$sql_create_purchase_returns_table = "
CREATE TABLE IF NOT EXISTS purchase_returns (
    purchase_return_id  INT(11) AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    return_date DATE NOT NULL,
    return_quantity INT(11) NOT NULL,
    return_amount DECIMAL(10,2) NOT NULL,
    return_reason TEXT NULL,
    payment_type VARCHAR(50) NULL,
    payment_status VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)";

// Create Sales Table
$sql_create_sales_table = "
CREATE TABLE IF NOT EXISTS sales (
    sale_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    product_id INT(11) NOT NULL,
    customer_id INT(11) NOT NULL,
    sale_quantity INT(11) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    sale_date DATE NOT NULL,
    sale_total DECIMAL(10,2) NOT NULL,
    payment_type VARCHAR(50) NULL,
    payment_status VARCHAR(50) NULL,
    is_returned TINYINT(1) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)";

// Create Sale Returns Table
$sql_create_sale_returns_table = "
CREATE TABLE IF NOT EXISTS sale_returns (
    sale_return_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    sale_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    return_date DATE NOT NULL,
    return_quantity INT(11) NOT NULL,
    return_amount DECIMAL(10,2) NOT NULL,
    return_reason TEXT NULL,
    payment_type VARCHAR(50) NULL,
    payment_status VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
)";

// Execute queries
$tables = [
    'Users Table' => $sql_create_users_table,
    'Categories Table' => $sql_create_categories_table,
    'Products Table' => $sql_create_products_table,
    'Suppliers Table' => $sql_create_suppliers_table,
    'Customers Table' => $sql_create_customers_table,
    'Purchases Table' => $sql_create_purchases_table,
    'Purchase Returns Table' => $sql_create_purchase_returns_table,
    'Sales Table' => $sql_create_sales_table,
    'Sale Returns Table' => $sql_create_sale_returns_table
];

foreach ($tables as $table_name => $query) {
    if (mysqli_query($connection, $query)) {
        echo "$table_name created successfully.<br>";
    } else {
        echo "Error creating $table_name: " . mysqli_error($connection) . "<br>";
    }
}

// Close the database connection
mysqli_close($connection);
?>
