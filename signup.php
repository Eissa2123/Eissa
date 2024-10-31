<?php
require('conn/conn.php');

if (isset($_SESSION['type']) && $_SESSION['type'] == 'Helpdesk') {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $department_id = $_POST['department'];
        $type = $_POST['type'];
        $email = $_POST['email'];

        // Check if the email already exists
        $email_check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $email_check_stmt->bind_param("s", $email);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();

        if ($email_check_result->num_rows > 0) {
            echo "<script>
                    alert('Email already exists. Please use a different email.');
                    window.location.href = 'signup.php'; // Redirect to signup page
                  </script>";
            exit();
        } else {
            // Prepare insert statement
            $stmt = $conn->prepare("INSERT INTO users (username, password, department, type, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiss", $username, $password, $department_id, $type, $email);

            if ($stmt->execute()) {
                echo "<script>
                alert('New user created successfully');
                window.location.href = 'signup.php'; // Redirect to signup page
              </script>";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $email_check_stmt->close();
        $conn->close();
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
        <title>Signup</title>

        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
            }
        </style>
    </head>

    <body>
        <div class="form">

            <div class="head-container-form">
                <div class="title-container">
                    <h2>Sign Up</h2>
                </div>
                <div class="btn-container">
                    <a href="help_home.php">
                        <button class="back">
                            <i class="fas fa-arrow-left"></i>
                            <div class="btn-title">BacK</div>
                        </button>
                    </a>
                </div>
            </div>

            <form method="post" action="">
                <div class="input-container-form">
                    <div class="forms">
                        <label for="username">User Name</label>
                    </div>

                    <div class="input-form">
                        <span class="material-symbols-outlined">person</span>
                        <input placeholder="Enter your username" class="input" id="username" name="username" type="text" required>
                    </div>
                </div>

                <div class="input-container-form">
                    <div class="forms">
                        <label for="password">Password</label>
                    </div>
                    <div class="input-form">
                        <span class="material-symbols-outlined">password</span>
                        <input placeholder="Enter your Password" class="input" id="password" name="password" type="password" required>
                    </div>
                </div>

                <div class="input-container-form">
                    <div class="forms">
                        <label for="department">Department</label>
                    </div>
                    <div class="input-form">
                        <span class="material-symbols-outlined">apartment</span>
                        <select id="department" name="department" class="input" required>
                            <?php
                            $sql = "SELECT id, name FROM departments";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>No departments available</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="input-container-form">
                    <div class="forms">
                        <label for="type">Type</label>
                    </div>
                    <div class="input-form">
                        <span class="material-symbols-outlined">format_list_bulleted</span>
                        <select id="type" name="type" class="input" required>
                            <option value="Requester">Requester</option>
                            <option value="Manager">Manager</option>
                            <option value="GM">GM</option>
                            <option value="Director">Director</option>
                            <option value="Receivers">Receivers</option>
                            <option value="Admin Receiver">Admin Receiver</option>
                            <option value="Delivery Note">Delivery Note</option>
                            <option value="Helpdesk">Helpdesk</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="input-container-form">
                    <div class="forms">
                        <label for="email">Email</label>
                    </div>
                    <div class="input-form">
                        <span class="material-symbols-outlined">alternate_email</span>
                        <input placeholder="Enter your Email" class="input" id="email" name="email" type="text" required>
                    </div>
                </div>


                <div class="submit-btn">
                    <button>
                        <input type="submit" value="Signup">
                        <div class="btn-title">Save</div>
                    </button>
                </div>
                <br>
                
            </form>
        </div>

        <?php include('layout/footer.php'); ?>
    <?php
} else {
    // If user type is not Helpdesk, redirect to login page
    header('Location:login.php');
    exit();
}
    ?>