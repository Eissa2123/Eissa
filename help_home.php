<?php
require('conn/conn.php');
$username = $_SESSION['username'];
if (isset($_SESSION['type']) && $_SESSION['type'] == 'Helpdesk') {
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
                display: block;
                margin: 20px;
            }

            .home-container {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                grid-auto-rows: minmax(100px, auto);
                gap: 10px;
                align-items: center;
            }

            .home-container>div {
                padding: 1rem;
                background-color: #fff;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border-radius: 20px;
            }

            .card10 {
                grid-column: 1/2;
                grid-row: 1/2;
            }

            .card20 {
                grid-column: 2/3;
                grid-row: 1/2;
            }

            .card30 {
                grid-column: 3/4;
                grid-row: 1/2;
            }

            .card40 {
                grid-column: 4/5;
                grid-row: 1/2;
            }

            .card50 {
                grid-column: 5/6;
                grid-row: 1/2;
            }

            .forget {
                float: left;
                padding-top: 10px;
            }

            .forget a {
                color: #292744;
            }

            .btn-container {
                padding-top: 27px;
                padding-bottom: 27px;
            }


            @media (max-width: 768px) {
                .home-container {
                    margin-top: 10px;
                    display: block;
                    margin-bottom: 10px;
                }

                .home-container>div {
                    display: flex;
                    justify-content: space-between;
                }
            }
        </style>
    </head>

    <body>
        <header class="header-container">
            <div class="header-contain">
                <div class="welcome">
                    <p>Hi <?php echo $_SESSION['username'] ?></p>
                    <h2>Welcom to Purchase Request System</h2>
                </div>

                <div class="right">
                    <div class="pic-container">
                        <a href="settings.php">
                            <button>
                                <i><img src="user_uploads/<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'images.png'; ?>" style="width: 45px; hight:45p; border-radius: 50%;" /></i>
                                <div class="pic-title"><?php echo $_SESSION['username'] ?></div>
                            </button>
                        </a>
                    </div>


                    <?php
                    $department_id = $_SESSION['department'];

                    $query = "SELECT d.name AS department FROM departments d WHERE d.id = '$department_id'";
                    $user_dep = $conn->query($query);
                    $user_depart = $user_dep->fetch_assoc()
                    ?>
                    <span style="color:#292744; background-color:rgba(131, 111, 255, .2); border-radius: 9999px; padding: 10px; font-size:13px"><?php echo $user_depart['department'] ?></span>
                </div>
            </div>
        </header>

        <main class="main-container">
            <div class="home-container">

                <div class="card10">
                    <div class="title">
                        <span class="material-symbols-outlined">person_add</span>
                        <p class="title-text">Add New User</p>
                    </div>
                    <div class="forget">
                        <a href="signup.php">Press Here</a>
                    </div>
                </div>

                <div class="card20">
                    <div class="title">
                        <span class="material-symbols-outlined">visibility</span>

                        <p class="title-text">All Users</p>
                    </div>
                    <div class="forget">
                        <a href="user_list.php">Press Here</a>
                    </div>
                </div>

                <div class="card30">
                    <div class="title">
                        <span class="material-symbols-outlined">category</span>
                        <p class="title-text">Add New Category</p>
                    </div>
                    <div class="forget">
                        <a href="add_category.php">Press Here</a>
                    </div>
                </div>

                <div class="card40">
                    <div class="title">
                        <span class="material-symbols-outlined">apartment</span>
                        <p class="title-text">Add New Department</p>
                    </div>

                    <div class="forget">
                        <a href="add_department.php">Press Here</a>
                    </div>
                </div>

                <div class="card50">
                    <div class="btn-container">
                        <a href="logout.php">
                            <button class="back">
                                <i class="material-symbols-outlined">logout</i>
                                <div class="btn-title">Logout</div>
                            </button>
                        </a>
                    </div>
                </div>

            </div>
        </main>

        <?php include('layout/footer.php'); ?>
    <?php
} else {
    // If user type is not Helpdesk, redirect to login page
    header('Location:login.php');
    exit();
}
    ?>