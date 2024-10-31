<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $verification_code = $_POST['verification_code'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    $conn = new mysqli('localhost', 'root', '', 'company2');

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE email='$email' AND verification_code='$verification_code'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $sql = "UPDATE users SET password='$new_password', verification_code=NULL WHERE email='$email'";
        if ($conn->query($sql) === TRUE) {
            echo "Password has been reset.";
            header('Location: login.php');
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Invalid verification code.";
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
    <title>Reset Your Password</title>
</head>

<body>

<div style="margin-top: 50px;"> </div>

<div class="conainer2">
    <h3>Reset Password</h3>
    </div>
    <div class="conainer2">
    <form method="post" action="reset_password.php">
        <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">
        <label for="verification_code">Verification Code:</label>
        <input type="text" id="verification_code" name="verification_code" required><br>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br>

        <input type="submit" value="Reset Password">

        <div style="font-size: 13px; text-align:center">To get verification code please connect admin or go back to <a href="login.php">Login</a></div>
    </form>
    </div>

    <?php @include('layout/footer.php'); ?>
