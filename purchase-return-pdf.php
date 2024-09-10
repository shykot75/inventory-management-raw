<?php
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

// Fetch purchase return details based on purchase_return_id from URL
if (!isset($_GET['purchase_return_id'])) {
    $_SESSION['error'] = 'No purchase return ID provided.';
    header('Location: purchase-return-list.php');
    exit();
}

$purchase_return_id = $_GET['purchase_return_id'];

// Fetch the purchase return details using mysqli_query
function getPurchaseReturn($connection, $purchase_return_id) {
    $query = "SELECT pr.return_date, pr.return_quantity, pr.return_amount, pr.payment_status, 
                     p.product_name, p.product_price, p.quantity as current_stock 
              FROM purchase_returns pr 
              JOIN products p ON pr.product_id = p.product_id 
              WHERE pr.purchase_return_id = $purchase_return_id LIMIT 1";

    $result = mysqli_query($connection, $query);
    return mysqli_fetch_assoc($result);
}

$purchaseReturn = getPurchaseReturn($connection, $purchase_return_id);

// Create PDF content
ob_start(); // Start output buffering to capture HTML content
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Purchase Return Order</title>
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
            <h2>Purchase Return Order</h2>
        </div>

        <table class="center-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Return Qty.</th>
                <th>Grand Total</th>
                <th>Payment Status</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo htmlspecialchars($purchaseReturn['return_date']); ?></td>
                <td>
                    <?php echo htmlspecialchars($purchaseReturn['product_name']); ?>
                    <small>Price: <?php echo htmlspecialchars($purchaseReturn['product_price']); ?> Tk</small>
                    <small>CS: <?php echo htmlspecialchars($purchaseReturn['current_stock']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($purchaseReturn['return_quantity']); ?></td>
                <td><?php echo htmlspecialchars($purchaseReturn['return_amount']); ?> Tk</td>
                <td><?php echo htmlspecialchars($purchaseReturn['payment_status']); ?></td>
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
$dompdf->stream('purchase_return_order_' . $purchase_return_id . '.pdf', ['Attachment' => false]);
