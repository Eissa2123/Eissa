<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $conn = new mysqli('localhost', 'root', '', 'company2');

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $verification_code = rand(100000, 999999);
        $sql = "UPDATE users SET verification_code='$verification_code' WHERE email='$email'";
        $conn->query($sql);

        mail($email, "Password Reset", "Your verification code is: $verification_code");

        echo "A verification code has been sent to your email.";
        header("Location: reset_password.php?email=$email");
    } else {
        echo "No user found with that email.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="assist/list.css">
    <title>Forgot Password</title>
</head>

<body>

    <div style="margin-top: 50px;"> </div>

    <div class="conainer2">
        <h3>Forgot Password</h3>
    </div>

    <div class="conainer2">
        <form method="post" action="forgot_password.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <input type="submit" value="Reset Password">

            <div style="font-size: 13px; text-align:center">You have your password <a href="login.php">Login</a></div>
        </form>
    </div>
    <?php @include('layout/footer.php'); ?>