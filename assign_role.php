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

// Fetch users with 'pending' type
$usersResult = $conn->query("SELECT * FROM users WHERE type = 'pending'");

// Fetch departments from departments table
$departmentsResult = $conn->query("SELECT id, name FROM departments");

$departments = [];
while ($row = $departmentsResult->fetch_assoc()) {
    $departments[$row['id']] = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="assist/new.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <title>Assign Roles to Pending Users</title>
    <style>
        body {
            display: flex;
            margin: 20px;
        }
        .btn-btn-container button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="container">
            <div class="head-container">
                <div class="title-container">
                    <h2>Assign Roles to Pending Users</h2>
                </div>
                <div class="btn-container">
                    <a href="user_list.php">
                        <button>
                            <i class="fas fa-arrow-left"></i>
                            <div class="btn-title">Back</div>
                        </button>
                    </a>
                </div>
            </div>

            <div class="table-container">
                <div class="body-table">
                    <?php if ($usersResult->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $usersResult->fetch_assoc()): ?>
                                    <tr>
                                        <form action="assign_role_process.php" method="POST">
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td>
                                                <select name="role">
                                                    <option value="Requester">Requester</option>
                                                    <option value="Manager">Manager</option>
                                                    <option value="Director">Director</option>
                                                    <option value="Admin Receiver">Admin Receiver</option>
                                                    <option value="Receiver">Receiver</option>
                                                    <option value="Helpdesk">Helpdesk</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="department">
                                                    <?php foreach ($departments as $id => $name): ?>
                                                        <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td class="btn-btn-container">
                                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
                                                
                                                <button type="submit" name="assign">
                                                <i class="fa-solid fa-diagram-project"></i>
                                                <div class="btn-title">Assign</div>  
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No pending users found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
