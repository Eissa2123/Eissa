<?php
@require('conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if 'id' and 'active' are set in the POST request
    if (isset($_POST['id']) && isset($_POST['active'])) {
        $user_id = $_POST['id'];
        $active = $_POST['active'];

        // Ensure the SQL query is correct
        $sql = "UPDATE users SET active = $active WHERE id = $user_id";
        
        if ($conn->query($sql)) {
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'Missing user ID or active status';
    }
} else {
    echo 'Invalid request method';
}

$conn->close();
?>