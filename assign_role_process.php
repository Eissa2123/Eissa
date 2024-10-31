<?php
session_start();
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'Helpdesk') {
    echo "Access denied.";
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'company2');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (isset($_POST['assign'])) {
    $email = $_POST['email'];  // Get the email from the hidden input
    $role = $_POST['role'];    // Get the selected role (string)
    $departmentId = $_POST['department'];  // Get the selected department (string)

    // Prepare the update query
    $stmt = $conn->prepare("UPDATE users SET type = ?, department = ?, active = 1 WHERE email = ?");
    $stmt->bind_param("sis", $role, $departmentId, $email);  // Note: "s" for string, "i" for integer
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Redirect to the assign role page with a success message
header('Location: assign_role.php?message=success');
exit();
?>
