<?php
// Include Composer's autoload to load the Dompdf library
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Start session and other logic
session_start();
include 'config.php'; // Include your DB connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch sale details based on sale_id from URL
if (!isset($_GET['sale_id'])) {
    $_SESSION['error'] = 'No sale ID provided.';
    header('Location: sales-list.php');
    exit();
}

$sale_id = $_GET['sale_id'];

// Fetch the sale details using mysqli_query
function getSale($connection, $sale_id) {
    $query = "SELECT s.sale_date, s.sale_quantity, s.sale_total, s.payment_status, s.is_returned,
                     p.product_name, p.product_price, p.quantity as current_stock,
                     c.customer_name
              FROM sales s 
              JOIN products p ON s.product_id = p.product_id
              JOIN customers c ON s.customer_id = c.customer_id
              WHERE s.sale_id = $sale_id LIMIT 1";

    $result = mysqli_query($connection, $query);
    return mysqli_fetch_assoc($result);
}

$sale = getSale($connection, $sale_id);

// Create PDF content
ob_start(); // Start output buffering to capture HTML content
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sale Order</title>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                background-color: #f4f4f4;
                font-size: 14px;
            }
            .container {
                background-color: #fff;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .header h2 {
                color: #000;
                margin: 0;
            }
            table, th, td {
                border: 1px solid black;
                border-collapse: collapse;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                color: #000;
            }
            .footer {
                display: flex;
                justify-content: space-between;
                margin-top: 20px;
                align-items: end;
            }
            .center-table {
                margin-left: auto;
                margin-right: auto;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="header">
            <h2>Sale Order</h2>
        </div>

        <table class="center-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Customer</th>
                <th>Sale Qty.</th>
                <th>Grand Total</th>
                <th>Payment Status</th>
                <th>Is Returned?</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                <td>
                    <?php echo htmlspecialchars($sale['product_name']); ?>
                    <small>Price: <?php echo htmlspecialchars($sale['product_price']); ?> Tk</small>
                    <small>CS: <?php echo htmlspecialchars($sale['current_stock']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($sale['sale_quantity']); ?></td>
                <td><?php echo htmlspecialchars($sale['sale_total']); ?> Tk</td>
                <td><?php echo htmlspecialchars($sale['payment_status']); ?></td>
                <td><?php echo $sale['is_returned'] == 1 ? 'YES' : 'NO'; ?></td>
            </tr>
            </tbody>
        </table>

    </div>
    </body>
    </html>

<?php
// Get the HTML content from the buffer and clear it
$html = ob_get_clean();

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // If you want to load external CSS, images, etc.

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');

// Render the PDF
$dompdf->render();

// Stream the PDF directly to the browser
$dompdf->stream('sale_order_' . $sale_id . '.pdf', ['Attachment' => false]);
