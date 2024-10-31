<?php
@require('conn/conn.php');

if (isset($_SESSION['type']) && $_SESSION['type'] == 'Helpdesk') {


    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];

        $add_department = "INSERT INTO departments (name) VALUES ('$name')";

        if ($conn->query($add_department) === TRUE) {
            echo "New department added successfully";
        } else {
            echo "Error: " . $add_department . "<br>" . $conn->error;
        }

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

        <title>Add Category</title>

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
                    <h2>Add Department</h2>
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
                        <label for="name">Department Name</label>
                    </div>

                    <div class="input-form">
                        <span class="material-symbols-outlined">apartment</span>
                        <input placeholder="Enter Depatment Name" class="input" id="name" name="name" type="text" required>
                    </div>
                </div>

                <div class="submit-btn">
                    <button>
                        <input type="submit" value="Add Department">
                        <div class="btn-title">Save</div>
                    </button>
                </div>
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