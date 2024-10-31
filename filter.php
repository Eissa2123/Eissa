<?php
// Start session and connect to the database
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'company');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get user type and department ID from session
$user_type = $_SESSION['type'];
$department_id = $_SESSION['department'];

// Initialize the query
$query = "SELECT pr.*, d.name AS department, c.category AS category 
        FROM purchase_requests pr 
        JOIN departments d ON pr.department_id = d.id 
        JOIN categories c ON pr.category = c.id WHERE 1=1";

// Add department condition if the user is not Admin Receiver
if ($user_type != 'Admin Receiver' && $user_type != 'Receivers') {
    $query .= " AND department_id = '$department_id'";
}

// Add filters for status and date range if provided
$conditions = [];

if (!empty($_GET['status'])) {
    $status = $_GET['status'];
    $conditions[] = "status = '$status'";
}

if (isset($_GET['from_date']) && isset($_GET['to_date']) && $_GET['from_date'] != '' && $_GET['to_date'] != '') {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
    $query .= " AND odate BETWEEN '" . $conn->real_escape_string($from_date) . "' AND '" . $conn->real_escape_string($to_date) . "'";
}

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
}

// Execute the query
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html>

<head>
    <title>List Requests</title>
    <style>
        /* Add your CSS styling here */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
            padding: 10px;
        }

        th {
            background-color: #f2f2f2;
        }

        button {
            margin: 5px;
            padding: 5px 10px;
        }
    </style>
</head>

<body>

    <h1>List Requests</h1>

    <!-- Filter Form -->
    <form method="GET" action="">
        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="">--Select Status--</option>
            <option value="New">New</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
        </select>

        <label for="from_date">From Date:</label>
        <input type="date" name="from_date" id="odate">

        <label for="to_date">To Date:</label>
        <input type="date" name="to_date" id="odate">

        <button type="submit">Filter</button>
    </form>

    <hr>

    <h2>Requests List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['odate'] . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>" . $row['department'] . "</td>";
                echo "<td>";

                // Allow status editing if the user is Admin Receiver
                if ($user_type == 'Admin Receiver') {
                    echo "<form method='POST' action='update_status.php'>";
                    echo "<input type='hidden' name='request_id' value='" . $row['id'] . "'>";
                    echo "<select name='new_status'>";
                    echo "<option value='New'>New</option>";
                    echo "<option value='In Progress'>In Progress</option>";
                    echo "<option value='Completed'>Completed</option>";
                    echo "</select>";
                    echo "<button type='submit'>Update</button>";
                    echo "</form>";
                } else {
                    // Display the status without editing options for Receivers
                    echo $row['status'];
                }

                echo "</td>";
        ?>
                <td>
                    <div class='e' method="post" action="">
                        <button type="button" onclick="eissa()">Details</button>
                        <script>
                            function eissa() {
                                window.location.href = 'print_purchase_request.php?id=<?php echo $request['id']; ?>';
                            }
                        </script>
                    </div>
                </td>
        <?php echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No requests found</td></tr>";
        }

        // Close the database connection
        $conn->close();
        ?>
    </table>

</body>

</html>



<nav>
    <!-- Back Menu and Logo -->
    <div class="logo">
        <i class='bx bx-menu meun_icon'></i>
        <span class="logo_pic"><img src="img/logo.png"></span>
    </div>

    <div class="user">
        <span style="padding: 2px; margin-right:5px; color:black"><?php echo $_SESSION['username'] ?></span>
        <br>

        <?php
        $department_id = $_SESSION['department'];

        $query = "SELECT d.name AS department FROM departments d WHERE d.id = '$department_id'";
        $user_dep = $conn->query($query);
        $user_depart = $user_dep->fetch_assoc()
        ?>
        <span style="color:#292744; background-color:#cfd5ee; border-radius: 7px; padding: 4px; font-size:15px"><?php echo $user_depart['department'] ?></span>

    </div>

    
    <!-- Front Sidebar -->
    <div class="sidebar">

        <!-- Front Menu and Logo -->
        <div class="logo">
            <i class='bx bx-menu meun_icon'></i>
            <span class="logo_pic"><img src="img/logo.png"></span>
        </div>

        <div class="sidebar_content">
            <ul class="lists">
                <li class="list">
                    <a href="user_fetch.php" class="nav-link">
                        <i class='bx bx-bell icon'></i>
                        <span class="link">Notification</span>
                    </a>
                </li>

                <li class="list">
                    <a href="home.php" class="nav-link">
                        <i class='bx bx-home-alt-2 icon'></i>
                        <span class="link">Home</span>
                    </a>
                </li>

                <li class="list">
                    <a href="list_requests.php" class="nav-link">
                        <i class='bx bx-list-ol icon'></i>
                        <span class="link">Purchase Requests</span>
                    </a>
                </li>

                <li class="list">
                    <?php if ($user_type === 'Requester'): ?>
                        <a href="purchase_request.php" class="nav-link">
                            <i class='bx bx-add-to-queue icon'></i>
                            <span class="link">Add Request</span>
                        </a>
                    <?php endif; ?>
                </li>
            </ul>

            <div class="bottom content">

                <li class="list">
                    <a href="settings.php" class="nav-link">
                        <i class='bx bx-cog icon'></i>
                        <span class="link">Settings</span>
                    </a>
                </li>

                <li class="list">
                    <a href="logout.php" class="nav-link">
                        <i class='bx bx-log-out icon'></i>
                        <span class="link">Logout</span>
                    </a>
                </li>
            </div>
        </div>
    </div>
</nav>

<section class="overlay"></section>

<script>
    const navBar = document.querySelector("nav"),
        menuBtns = document.querySelectorAll(".meun_icon"),
        overlay = document.querySelector(".overlay");
    console.log(navBar, menuBtns, overlay);

    menuBtns.forEach(menuBtns => {
        menuBtns.addEventListener("click", () => {
            navBar.classList.toggle("open");
        });
    });

    overlay.addEventListener("click", () => {
        navBar.classList.remove("open");
    });
</script>