<?php
@require('conn/conn.php');

$user_type = $_SESSION['type'];
$department_id = $_SESSION['department'];

$notifications = [];

// Fetch notifications based on user type
if ($user_type == 'Admin Receiver') {
    $sql = "SELECT * FROM purchase_requests WHERE notification_status = 0 AND notification_type = 'new'";
} elseif ($user_type == 'Receivers') {
    $sql = "SELECT * FROM purchase_requests WHERE notification_status = 0 AND notification_type = 'new'";
} else {
    $sql = "SELECT * FROM purchase_requests WHERE notification_status = 0 AND notification_type = 'updated' AND department_id = $department_id";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

// If notifications exist, mark them as seen
if (!empty($notifications)) {
    $notification_ids = implode(',', array_column($notifications, 'id'));
   $conn->query("UPDATE purchase_requests SET notification_status = 1 WHERE id IN ($notification_ids)");
}

 
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh every 10 seconds -->
    <style>
        #notification-container {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 9999;
        }

        .notification-popup {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s;
        }

        .notification-popup h4 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        .notification-popup p {
            margin: 0;
            font-size: 14px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div id="notification-container">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-popup">
                    <h4>
                        <?php echo $notification['notification_type'] == 'new' ? 'New Request' : 'Updated Request'; ?>
                    </h4>
                    <p>Request Reference: <?php echo $notification['reference']; ?></p>
                    <p>Request Status: <?php echo $notification['status']; ?></p>
                    <p>Date: <?php echo $notification['odate']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>