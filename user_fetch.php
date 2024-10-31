<?php
$conn = new mysqli('localhost', 'root', '', 'company2');
session_start();

$user_type = $_SESSION['type'];
$department_id = $_SESSION['department'];

$notifications = [];

// Fetch notifications based on user type, ordered by date descending
if ($user_type == 'Admin Receiver' || $user_type == 'Receivers') {
    $sql = "SELECT * FROM purchase_requests WHERE notification_status = 1 AND notification_type = 'new' ORDER BY odate DESC";
} else {
    $sql = "SELECT * FROM purchase_requests WHERE notification_status = 1 AND notification_type = 'updated' AND department_id = $department_id ORDER BY odate DESC";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }

        .container{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h4{
            text-decoration: underline;
            font-size: 20px;
            color: #4b4caa;
        }
        a{
            width: 85px;
            padding: 10px 10px;
            text-align: center;
            text-decoration: none;
            background-color: #4b4caa;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        a:hover{
            background-color: #6975c8;
        }


        #notification-container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .notification-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item h4 {
            margin: 0 0 5px;
        }

        .notification-item p {
            margin-bottom: 1px;
            font-size: 14px;
            font-weight: 600;
        }

        .notification-item span{
            font-size: 14px;
            font-weight: 100;
            color: gray;
        }
    </style>
</head>

<body>
<div class="container">
<h1>Notifications</h1>
    <a href="home.php"> Back</a>
</div>

    <div id="notification-container">
        <?php if (!empty($notifications)): ?>
            <ul>
                <?php foreach ($notifications as $notification): ?>
                    <li class="notification-item">
                        <h4>
                            <?php echo $notification['notification_type'] == 'new' ? 'New Request' : 'Updated Request'; ?>
                        </h4>
                        <p>Request Reference</p>
                        <span><?php echo htmlspecialchars($notification['reference']); ?></span>

                        <p>Request Status</p>
                        <span><?php echo htmlspecialchars($notification['status']); ?></span>

                        <p>Date</p>
                        <span><?php echo htmlspecialchars($notification['odate']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No new notifications.</p>
        <?php endif; ?>
    </div>
</body>

</html>