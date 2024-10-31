<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'company');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$department_id = $_SESSION['department'];
$user_type = $_SESSION['type'];

// Define queries based on user type
if ($user_type === 'Requester') {
// Fetch category data
$sql = "SELECT c.category, COUNT(pr.id) as count 
        FROM purchase_requests pr 
        JOIN categories c ON pr.category = c.id 
        WHERE pr.department_id='$department_id' 
        GROUP BY pr.category";
$result = $conn->query($sql);

$labels = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['category'];
    $counts[] = $row['count'];
}

echo json_encode(['labels' => $labels, 'counts' => $counts]);

} else {
    $sql = "SELECT c.category, COUNT(pr.id) as count 
    FROM purchase_requests pr 
    JOIN categories c ON pr.category = c.id 
     
    GROUP BY pr.category";
$result = $conn->query($sql);

$labels = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
$labels[] = $row['category'];
$counts[] = $row['count'];
}

echo json_encode(['labels' => $labels, 'counts' => $counts]);} 

?>


