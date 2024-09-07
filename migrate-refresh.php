<?php
include 'config.php';  // Include the database connection

// List of tables to drop (child tables first)
$tables = [
    'purchase_returns',  // Dependent on 'purchases'
    'purchases',         // Dependent on 'suppliers'
    'sale_returns',      // Dependent on 'sales'
    'sales',             // Dependent on 'customers'
    'users',
    'products',
    'categories',
    'customers',
    'suppliers'          // Parent tables last
];

// Loop through each table and drop it
foreach ($tables as $table) {
    $drop_query = "DROP TABLE IF EXISTS $table";
    if (mysqli_query($connection, $drop_query)) {
        echo "Dropped table '$table' successfully.<br>";
    } else {
        echo "Error dropping table '$table': " . mysqli_error($connection) . "<br>";
    }
}

// Close the database connection
mysqli_close($connection);

// After running this script, run your migrate.php file to recreate the tables
?>
