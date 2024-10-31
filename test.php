<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'company');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    header('Location: home.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the account is active
        if ($row['active'] == 1) {
            // Verify the password
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['department'] = $row['department'];
                $_SESSION['type'] = $row['type'];

                // Redirect based on user type
                if ($row['type'] === 'Helpdesk') {
                    header('Location: help_home.php');
                } else {
                    header('Location: home.php');
                }
                exit();
            } else {
                echo "<script>alert('Username or password is incorrect');</script>";
            }
        } else {
            echo "<script>alert('Your account is inactive. Please contact support.');</script>";
        }
    } else {
        echo "<script>alert('Username or password is incorrect');</script>";
    }
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

    <title>Login</title>

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
                <h2>Login</h2>
            </div>
        </div>

        <form method="post" action="login.php">
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

            <div class="forget">
                <a href="forgot_password.php">Forgot your password?</a>
            </div>

            <div class="submit-btn">
                <button>
                    <input type="submit" value="Login">
                    <div class="btn-title">Save</div>
                </button>
            </div>

            <p class="p">Don't have an account? <span class="contact">Contact your Manager System</span>
        </form>

    </div>
    <?php @include('layout/footer.php'); ?>


</html>