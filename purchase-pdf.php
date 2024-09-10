<?php
session_start();
require 'vendor/autoload.php'; // Include the Dompdf library

use Dompdf\Dompdf;
use Dompdf\Options;

include 'config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get purchase_id from the URL
if (!isset($_GET['purchase_id'])) {
    $_SESSION['error'] = 'No purchase ID provided.';
    header('Location: purchase-list.php');
    exit();
}

$purchase_id = $_GET['purchase_id'];

// Fetch purchase details
function getPurchaseDetails($connection, $purchase_id) {
    $query = "SELECT p.purchase_id, p.purchase_date, p.purchase_quantity, p.purchase_total, p.payment_status, p.is_returned, 
                     prod.product_name, prod.product_price, prod.quantity as current_stock, 
                     supp.supplier_name
              FROM purchases p
              JOIN products prod ON p.product_id = prod.product_id
              JOIN suppliers supp ON p.supplier_id = supp.supplier_id
              WHERE p.purchase_id = $purchase_id";
    $result = mysqli_query($connection, $query);
    return mysqli_fetch_assoc($result);
}

$purchase = getPurchaseDetails($connection, $purchase_id);

if (!$purchase) {
    $_SESSION['error'] = 'Invalid purchase details.';
    header('Location: purchase-list.php');
    exit();
}

// Create an instance of Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$dompdf = new Dompdf($options);

// HTML content for the PDF
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
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
            padding: 10px;
        }

        .center-table {
            margin-left: auto;
            margin-right: auto;
        }

        .footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .signature {
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Purchase Order</h2>
        </div>

        <table class="center-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Supplier</th>
                    <th>Purchase Qty.</th>
                    <th>Grand Total</th>
                    <th>Payment Status</th>
                    <th>Is Returned?</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . htmlspecialchars($purchase['purchase_date']) . '</td>
                    <td>' . htmlspecialchars($purchase['product_name']) . '<br>
                        <small>Price: ' . htmlspecialchars($purchase['product_price']) . ' Tk</small><br>
                        <small>CS: ' . htmlspecialchars($purchase['current_stock']) . '</small>
                    </td>
                    <td>' . htmlspecialchars($purchase['supplier_name']) . '</td>
                    <td>' . htmlspecialchars($purchase['purchase_quantity']) . '</td>
                    <td>' . htmlspecialchars($purchase['purchase_total']) . ' Tk</td>
                    <td>' . htmlspecialchars($purchase['payment_status']) . '</td>
                    <td>' . ($purchase['is_returned'] == 1 ? 'YES' : 'NO') . '</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
';

// Load HTML content into Dompdf
$dompdf->loadHtml($html);

// (Optional) Setup the paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF (streaming to the browser)
$dompdf->stream('purchase_details_' . uniqid() . '.pdf', ['Attachment' => false]);
