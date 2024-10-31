<?php
include 'conn/conn.php';

$request_id = $_GET['id'];

$query = "SELECT r.*, d.name AS department_name FROM purchase_requests r 
          JOIN departments d ON r.department_id = d.id 
          WHERE r.id = '$request_id'";
$result = $conn->query($query);
$request = $result->fetch_assoc();
?>

<p><strong>Reference:</strong> <?php echo $request['reference']; ?></p>
<p><strong>Request Date:</strong> <?php echo $request['odate']; ?></p>
<p><strong>Department:</strong> <?php echo $request['department_name']; ?></p>
<p><strong>Priority:</strong> <?php echo $request['priority']; ?></p>
<p><strong>Status:</strong> <?php echo $request['status']; ?></p>