<?php
@require('conn/conn.php');

$users = "SELECT u.*, d.name AS department FROM users u
JOIN departments d ON u.department = d.id";
$user_list = $conn->query($users);


$uploads_dir = "user_uploads/";
$default_pic = "user_uploads/images.png";

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
    <title>Home</title>

    <style>
        body {
            display: flex;
            margin: 20px;
        }
    </style>
</head>

<body>

    <div class="main-container">
        <div class="container">
            <div class="head-container">
                <div class="title-container">
                    <h2>Users List</h2>
                </div>
                <div class="btn-container">
                    <a href="help_home.php">
                        <button>
                            <i class="fas fa-arrow-left"></i>
                            <div class="btn-title">BacK</div>
                        </button>
                    </a>
                </div>
            </div>


            <div class="table-container">
                <div class="head-table">
                    <div class="btn-container">
                        <a href="signup.php">
                            <button class="">
                                <i class="fas fa-plus"></i>
                                <div class="btn-title">Add New User</div>
                            </button>
                        </a>
                    </div>

                    <div class="btn-container">
                        <a href="assign_role.php">
                            <button class="">
                                <i class="fa-solid fa-diagram-project"></i>
                                <div class="btn-title">Assign Users Role</div>
                            </button>
                        </a>
                    </div>
                </div>
                <hr>
                <div class="body-table">
                    <?php
                    if ($user_list->num_rows > 0) { ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Picture</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($show = $user_list->fetch_assoc()) {
                                    $profile_pic = !empty($show['profile_pic']) ? $uploads_dir . htmlspecialchars($show['profile_pic']) : $default_pic;
                                    $isActive = $show['active'] == 1;
                                ?>
                                    <tr>
                                        <td data-label='Profile Picture:'><img src="<?php echo ($profile_pic); ?>" alt="" style="width:30px;height:30px;"></td>
                                        <td data-label='User Name:'><?php echo htmlspecialchars($show['username']); ?></td>
                                        <td data-label='Email:'><?php echo htmlspecialchars($show['email']); ?></td>
                                        <td data-label='Department:'><?php echo htmlspecialchars($show['department']); ?></td>
                                        <td data-label='Type:'><?php echo htmlspecialchars($show['type']); ?></td>
                                        <td data-label='Status:'>
                                            <div class="switch-button <?php echo $isActive ? 'active' : ''; ?>" onclick="toggleStatus(<?php echo $show['id']; ?>, this)">
                                                <div class="slider"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- Additional actions (e.g., Edit, Delete) -->
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php  } else {
                    }


                    $conn->close();

                    ?>


                    <script>
                        function toggleStatus(userId, element) {
                            // Send AJAX request to toggle status (use fetch or XMLHttpRequest)
                            var isActive = element.classList.contains('active') ? 0 : 1;

                            fetch('update_user.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'id=' + userId + '&active=' + isActive
                                })
                                .then(response => response.text())
                                .then(data => {
                                    if (data === 'success') {
                                        element.classList.toggle('active');
                                    } else {
                                        alert('Error updating status');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                });
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>