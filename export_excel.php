<?php
require('conn/conn.php');
require('phpspreadsheet/vendor/autoload.php'); 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch user type and department ID from session
$user_type = $_SESSION['type'];
$department_id = $_SESSION['department'];

// Get filters from GET request
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';


// Base query
$query = "SELECT pr.*, d.name AS department, pri.item_name, pri.quantity, pri.unit, pri.description, pri.picture 
          FROM purchase_requests pr 
          JOIN departments d ON pr.department_id = d.id 
          LEFT JOIN purchase_request_items pri ON pr.id = pri.purchase_request_id 
          WHERE 1=1 ";

// Apply filters
$conditions = [];
if (!empty($status_filter)) {
    $conditions[] = "pr.status = '$status_filter'";
}
if (!empty($start_date) && !empty($end_date)) {
    $conditions[] = "pr.odate BETWEEN '$start_date' AND '$end_date'";
}
if (!empty($reference)) {
    $conditions[] = "pr.reference LIKE '%$reference%'";
}
if ($user_type != 'Admin Receiver' && $user_type != 'Receivers' && $user_type != 'Delivery Note') {
    $conditions[] = "pr.department_id = '$department_id'";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(' AND ', $conditions);
}

$query .= " ORDER BY pr.odate DESC";

// Execute query
$result = $conn->query($query);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers
$headers = [
    'Reference', 'Request Date', 'Department', 'Priority', 'Status',
    'Item Name', 'Quantity', 'Unit', 'Description', 'Picture'
];
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Write data to sheet
$rowNumber = 2;
while ($row = $result->fetch_assoc()) {
    $col = 'A';
    $sheet->setCellValue($col++ . $rowNumber, $row['reference']);
    $sheet->setCellValue($col++ . $rowNumber, $row['odate']);
    $sheet->setCellValue($col++ . $rowNumber, $row['department']);
    $sheet->setCellValue($col++ . $rowNumber, $row['priority']);
    $sheet->setCellValue($col++ . $rowNumber, $row['status']);
    $sheet->setCellValue($col++ . $rowNumber, $row['item_name']);
    $sheet->setCellValue($col++ . $rowNumber, $row['quantity']);
    $sheet->setCellValue($col++ . $rowNumber, $row['unit']);
    $sheet->setCellValue($col++ . $rowNumber, $row['description']);
    $sheet->setCellValue($col++ . $rowNumber, $row['picture']);
    $rowNumber++;
}

// Set file name and download it
$writer = new Xlsx($spreadsheet);
$fileName = 'purchase_requests_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
?>
