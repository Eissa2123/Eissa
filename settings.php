<?php
require('conn/conn.php');

$username = $_SESSION['username'];

// Fetch user details
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_username = $_POST['new_username'];
        $new_email = $_POST['new_email'];

        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL) || !preg_match('/@alj\.com$/', $new_email)) {
            echo "<script>alert('Email must end with @alj.com');</script>";
        } else {
            // Handle file upload
            if (!empty($_FILES['profile_pic']['name'])) {
                $upload_dir = 'user_uploads/';
                $upload_file = $upload_dir . basename($_FILES['profile_pic']['name']);
                $upload_ok = 1;
                $imageFileType = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));

                // Check if image file is a actual image or fake image
                $check = getimagesize($_FILES['profile_pic']['tmp_name']);
                if ($check === false) {
                    echo "<script>alert('File is not an image.');</script>";
                    $upload_ok = 0;
                }

                // Check file size
                if ($_FILES['profile_pic']['size'] > 500000) {
                    echo "<script>alert('Sorry, your file is too large.');</script>";
                    $upload_ok = 0;
                }

                // Allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
                    $upload_ok = 0;
                }

                // Check if $upload_ok is set to 0 by an error
                if ($upload_ok == 0) {
                    echo "<script>alert('Sorry, your file was not uploaded.');</script>";
                } else {
                    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_file)) {
                        // Update user profile picture
                        $profile_pic = basename($_FILES['profile_pic']['name']);
                    } else {
                        echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
                    }
                }
            }


            $sql = "UPDATE users SET username = ?, email = ?" . (isset($profile_pic) ? ", profile_pic = ?" : "") . " WHERE username = ?";
            $stmt = $conn->prepare($sql);
            if (isset($profile_pic)) {
                $stmt->bind_param("ssss", $new_username, $new_email, $profile_pic, $username);
            } else {
                $stmt->bind_param("sss", $new_username, $new_email, $username);
            }
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username; // Update session username
                echo "<script>alert('Profile updated successfully');</script>";
            } else {
                echo "<script>alert('Error updating profile');</script>";
            }
            $stmt->close();
        }
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            echo "<script>alert('New passwords do not match');</script>";
        } else {
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET password = ? WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $hashed_password, $username);
                if ($stmt->execute()) {
                    echo "<script>alert('Password changed successfully');</script>";
                } else {
                    echo "<script>alert('Error changing password');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('Current password is incorrect');</script>";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Document</title>
    <style>
        .hidden {
            display: none;
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap');

        * {
            font-family: "Poppins", sans-serif;
        }

        .container {
            display: flex;
            margin: 50px 10px;
        }

        .profile {
            flex: 1;
            margin-right: 10px;
            background-color: white;
            box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
            border-radius: 5px;
            height: max-content;
        }

        .profile-header {
            display: flex;
            margin-left: 15px;
        }

        .profile-img {
            width: 60px;
            height: 60px;
            border-radius: 35px;
            margin: 10px;
        }

        .profile-text-container {
            line-height: 0.5;
        }

        .menu {
            margin: 0 20px;
        }

        .menu-link {
            display: block;
            text-decoration: none;
            color: #768499;
            padding: 10px;
            margin: 10px;
            border-radius: 10px;
            font-size: 15px;
            font-family: "Poppins", sans-serif;
        }

        .material-symbols-outlined {
            margin-right: 5px;
            font-size: 17px;
        }

        .menu-link {
            color: white;
            background: #4b4caa;
        }

        .menu-link:hover {
            background-color: #6975c8;
            color: white;
        }

        .account {
            flex: 2;
            margin-left: 10px;
            background-color: white;
            box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
            border-radius: 5px;
            height: max-content;
        }

        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        .account-title {
            font-size: 20px;
            font-weight: 600;
            margin-left: 10px;
        }

        .btn-container {
            display: flex;
            flex-direction: row;
            justify-content: right;
            margin: 18px;
        }

        .btn-save {
            width: 100px;
            height: 40px;
            cursor: pointer;
            border-radius: 10px;
            border: none;
            color: white;
            background-color: #4b4caa;
            margin-right: 5px;
            transition-duration: 0.3s;
            box-shadow: rgba(0, 0, 0, 0.1);
        }

        .btn-cancel:hover,
        .btn-save:hover {
            background-color: #6975c8;
        }

        .account-edit {
            display: block;
            justify-content: space-between;
            margin: 15px 0;
            margin-right: 35px;
        }

        .input-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            margin: 20px 20px;
        }

        .input-container label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .input-container input {
            font-size: 12px;
            font-weight: 300;
            border-radius: 5px;
            border: 1px solid;
            height: 25px;
            padding: 5px;
        }

        .input-container input:focus {
            outline: none;
            border: 1px solid;
        }


        .pic-container {
            width: 100%;
            display: flex;
            flex-direction: row;
            margin: 20px 20px;
            align-items: end;
            justify-content: center;
        }

        .pic-container label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .pic-container .prof-img {
            display: flex;
            flex-direction: column;
            font-size: 14px;
            font-weight: 500;
        }

        .pic-container .img {
            width: 90px;
            height: 90px;
            border-radius: 50%;

        }

        .pic-container input {
            font-size: 12px;
            font-weight: 300;
            border: 0px solid;
            width: 79px;
            padding: 5px;
        }

        @media screen and (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .profile {
                margin-bottom: 20px;
                margin-right: 0;
            }

            .account {
                margin: 0;
            }

            .account-header {
                flex-direction: column;
                margin: 0;
            }

            .account-edit {
                flex-direction: column;
                margin: 10px;
            }

            .input-container {
                margin: 10px;
            }

            .input-container input {
                margin-right: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="profile">
            <div class="profile-header">
                <img src="user_uploads/<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'images.png'; ?>"
                    class="profile-img" />
                <div class="profile-text-container">
                    <h3 class="profile-title">Settings</h3>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>

            <div class="menu">
                <a href="#" class="menu-link" onclick="toggleForm('account-general'); return false;">
                    <span class='material-symbols-outlined'>manage_accounts</span>
                    <i class="menu-lable">Account</i>
                </a>
                <a href="#" class="menu-link" onclick="toggleForm('account-change-password'); return false;">
                    <span class='material-symbols-outlined'>Password</span>
                    <i class="menu-lable">Change Password</i>
                </a>
                <a href="<?php echo ($_SESSION['type'] === 'Helpdesk') ? 'help_home.php' : 'home.php'; ?>" class="menu-link">
                    <span class='material-symbols-outlined'>cancel</span>
                    <i class="menu-label">Cancel</i>
                </a>
                <a href="logout.php" class="menu-link">
                    <span class='material-symbols-outlined'>logout</span>
                    <i class="menu-lable">logout</i>
                </a>
            </div>
        </div>

        <form class="account" id="account-general" method="post" enctype="multipart/form-data">
            <div class="account-header">
                <div class="account-title">
                    <h3>Account Setting</h3>
                </div>
            </div>
            <div class="account-edit">
                <div class="pic-container">
                    <div class="prof-img">
                        <label for="picture">Profile Picture</label>
                        <img src="user_uploads/<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'images.png'; ?>" class="img">
                    </div>
                    <input type="file" id="picture" name="profile_pic">
                </div>
                <div class="input-container">
                    <label for="username">User Name</label>
                    <input type="text" id="username" name="new_username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="input-container">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                </div>
                <div class="btn-container">
                    <button class="btn-save" type="submit" name="update_profile" value="Update Profile">Save</button>
                </div>
            </div>
        </form>

        <form class="account hidden" id="account-change-password" method="post">
            <div class="account-header">
                <div class="account-title">
                    <h3>Password Setting</h3>
                </div>
            </div>
            <div class="account-edit">
                <div class="input-container">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="input-container">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="input-container">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="btn-container">
                    <button class="btn-save" type="submit" name="change_password">Save</button>
                </div>

        </form>
    </div>

    <script>
        function eissa() {
            window.location.href('home.php')
        }

        function toggleForm(formId) {
            const generalForm = document.getElementById('account-general');
            const passwordForm = document.getElementById('account-change-password');

            if (formId === 'account-general') {
                generalForm.classList.remove('hidden');
                passwordForm.classList.add('hidden');
            } else {
                passwordForm.classList.remove('hidden');
                generalForm.classList.add('hidden');
            }
        }
    </script>
</body>

</html>