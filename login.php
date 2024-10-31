<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'company2');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    header('Location: home.php');
    exit();
}

$messageType = '';
$messageContent = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists using email instead of username
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the account is active
        if ($row['active'] == 1) {
            // Verify the password
            if (password_verify($password, $row['password'])) {
                $_SESSION['username'] = $row['username'];  // Store the username in session
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
                $messageType = 'error';
                $messageContent = 'Email or password is incorrect';
            }
        } else {
            $messageType = 'warning';
            $messageContent = 'Your account is inactive. Please contact support.';
        }
    } else {
        $messageType = 'error';
        $messageContent = 'Email or password is incorrect';
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

    <title>Login</title>

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .micro {
            display: flex;
            align-items: center;
        }

        .micro img{
            width: 30px;
            padding-right: 10px;
        }

        .micro span{
            font-size: 17px;
            font-weight: 200;

        }
    </style>
</head>

<body>

    <div class="form">
        <form method="post" action="microsoft_login.php">
            <div class="submit-btn">

                <button type="submit" class="micro">
                    <div><img src="img/microsoft.png" alt=""></div>
                    <span>Login With Your Organization Microsoft Account</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Include the popup message -->
    <?php include('popup_message.php'); ?>

    <script>
        // Show the popup if there's a message to display
        <?php if ($messageType && $messageContent): ?>
            showPopup('<?php echo $messageType; ?>', '<?php echo addslashes($messageContent); ?>');
        <?php elseif (isset($_GET['message']) && $_GET['message'] == 'awaiting_approval'): ?>
            showPopup('warning', 'Your account is awaiting approval');
        <?php endif; ?>
    </script>


    <?php @include('layout/footer.php'); ?>
</body>

</html>