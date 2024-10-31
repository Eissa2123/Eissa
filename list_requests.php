<?php
include 'fetch_notifications.php';

@require('conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['id'];
    $status = $_POST['status'];
    $priority = $_POST['priority']; // New priority handling

    $sql = "UPDATE purchase_requests SET status='$status', priority='$priority', notification_status = 0, notification_type = 'updated' WHERE id='$request_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Request updated successfully.";
    } else {
        $error = "Error updating request: " . $conn->error;
    }
}

// Get user type and department ID from session
$user_type = $_SESSION['type'];
$department_id = $_SESSION['department'];

// Filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';

// Number of requests per page
$requests_per_page = 15;

// Get the current page number from URL, if not present, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL query
$offset = ($page - 1) * $requests_per_page;

// Base query
$query = "SELECT r.*, d.name AS department FROM purchase_requests r 
          JOIN departments d ON r.department_id = d.id ";

// Apply filters
$conditions = [];
if (!empty($status_filter)) {
    $conditions[] = "r.status = '$status_filter'";
}
if (!empty($start_date) && !empty($end_date)) {
    $conditions[] = "r.odate BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($reference)) {
    $conditions[] = "r.reference LIKE '%$reference%'";
}

if ($user_type != 'Admin Receiver' && $user_type != 'Receivers' && $user_type != 'Delivery Note') {
    // For non-admin and non-delivery users, add department filter
    $conditions[] = "r.department_id = '$department_id'";
}

// If there are any conditions, add them to the query
if (!empty($conditions)) {
    $query .= "WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY r.odate DESC LIMIT $offset, $requests_per_page";

// Get the total number of requests for pagination
$count_query = "SELECT COUNT(*) AS total FROM purchase_requests r 
                JOIN departments d ON r.department_id = d.id ";

if (!empty($conditions)) {
    $count_query .= "WHERE " . implode(' AND ', $conditions);
}

$total_requests_result = $conn->query($count_query);
$total_requests_row = $total_requests_result->fetch_assoc();
$total_requests = $total_requests_row['total'];

// Calculate the total number of pages
$total_pages = ceil($total_requests / $requests_per_page);

// Fetch the requests for the current page
$result = $conn->query($query);

?>

<?php @include('layout/header.php'); ?>

<div class="main-container">
    <div class="container">
        <div class="head-container">
            <div class="title-container">
                <h2>Requests List</h2>
            </div>
            <div class="btn-container">
                <a href="home.php">
                    <button>
                        <i class="fas fa-arrow-left"></i>
                        <div class="btn-title">BacK</div>
                    </button>
                </a>
            </div>
        </div>

        <?php if (isset($message)) : ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if (isset($error)) : ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>


        <form method="GET" action="">
            <div class="filter-container">
                <div class="input-container">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="">All</option>
                        <option value="New" <?php if ($status_filter == 'New') echo 'selected'; ?>>New</option>
                        <option value="Waiting Approval" <?php if ($status_filter == 'Waiting Approval') echo 'selected'; ?>>Waiting Approval</option>
                        <option value="On Progress" <?php if ($status_filter == 'On Progress') echo 'selected'; ?>>On Progress</option>
                        <option value="Delivery On Progress" <?php if ($status_filter == 'Delivery On Progress') echo 'selected'; ?>>Delivery On Progress</option>
                        <option value="Completed" <?php if ($status_filter == 'Completed') echo 'selected'; ?>>Completed</option>
                        <option value="Rejected" <?php if ($status_filter == 'Rejected') echo 'selected'; ?>>Rejected</option>
                    </select>
                </div>
                <div class="input-container">
                    <label for="start_date">From Date:</label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="input-container">
                    <label for="end_date">To Date:</label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="input-container">
                    <label for="reference">Search by Reference:</label>
                    <input type="text" name="reference" id="reference" value="<?php echo $reference; ?>" placeholder="Enter Request Reference">
                </div>
                <div class="btn-container">
                    <button type="submit" name="submit" class="filter-btn">
                        <i class="fas fa-filter"></i>
                        <div class="btn-title">Filter</div>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="table-container">
        <div class="head-table">
            <div class="btn-container">
                <?php if ($user_type == 'Requester') { ?>
                    <a href="purchase_request.php">
                        <button class="add_new">
                            <i class="fas fa-plus"></i>
                            <div class="btn-title">New Request</div>
                        </button>
                    </a>
                <?php } ?>
            </div>
            <div class="btn-container">
                <a href="requests_sheet.php">
                    <button class="">
                        <i class="fas fa-eye"></i>
                        <div class="btn-title">View All Requests</div>
                    </button>
                </a>
            </div>
        </div>
        <hr>
        <div class="body-table">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Request Date</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td data-label = 'Reference:'>" . $row['reference'] . "</td>";
                            echo "<td data-label = 'Request Date:'>" . $row['odate'] . "</td>";
                            echo "<td data-label = 'Department:'>" . $row['department'] . "</td>";
                            echo "<td data-label = 'Priority:'>";

                            // Priority for non-delivery users
                            if ($user_type == 'Admin Receiver') { ?>
                                <!-- Priority Dropdown for Admin Receiver -->
                                <form method="POST" action="">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <select name="priority" class="priority-select" onchange="this.form.submit()">
                                        <option value="Low" <?php if ($row['priority'] == 'Low') echo 'selected'; ?>>Low</option>
                                        <option value="Medium" <?php if ($row['priority'] == 'Medium') echo 'selected'; ?>>Medium</option>
                                        <option value="High" <?php if ($row['priority'] == 'High') echo 'selected'; ?>>High</option>
                                    </select>
                                <?php } else {
                                if ($row['priority'] == 'Low') {
                                    echo "<p class='low'>Low</p>";
                                } elseif ($row['priority'] == 'Medium') {
                                    echo "<p class='medium'>Medium</p>";
                                } elseif ($row['priority'] == 'High') {
                                    echo "<p class='high'>High</p>";
                                }
                            }

                            echo "</td>";
                            echo "<td data-label = 'Status:'>";

                            // Status Dropdown for Admin Receiver
                            if ($user_type == 'Admin Receiver') { ?>
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="New" <?php if ($row['status'] == 'New') echo 'selected'; ?>>New</option>
                                        <option value="Waiting Approval" <?php if ($row['status'] == 'Waiting Approval') echo 'selected'; ?>>Waiting Approval</option>
                                        <option value="On Progress" <?php if ($row['status'] == 'On Progress') echo 'selected'; ?>>On Progress</option>
                                        <option value="Delivery On Progress" <?php if ($row['status'] == 'Delivery On Progress') echo 'selected'; ?>>Delivery On Progress</option>
                                        <option value="Completed" <?php if ($row['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                                        <option value="Rejected" <?php if ($row['status'] == 'Rejected') echo 'selected'; ?>>Rejected</option>
                                    </select>
                                </form>
                            <?php } else {
                                if ($row['status'] == 'New') {
                                    echo "<p style='color: #060542; background-color: #776ae9; border-radius:10px; padding:4px;'>New</p>";
                                } elseif ($row['status'] == 'Waiting Approval') {
                                    echo "<p style='color: black; background-color: #ff9f40; border-radius:10px; padding:4px;'>Waiting Approval</p>";
                                } elseif ($row['status'] == 'On Progress') {
                                    echo "<p style='color: black; background-color: #ffdd40; border-radius:10px; padding:4px;'>On Progress</p>";
                                } elseif ($row['status'] == 'Delivery On Progress') {
                                    echo "<p style='color: black; background-color: #19efb9; border-radius:10px; padding:4px;'>Delivery On Progress</p>";
                                } elseif ($row['status'] == 'Completed') {
                                    echo "<p style='color: black; background-color: #40ff73; border-radius:10px; padding:4px;'>Completed</p>";
                                } elseif ($row['status'] == 'Rejected') {
                                    echo "<p style='color: white; background-color: #ff0000; border-radius:10px; padding:4px;'>Rejected</p>";
                                }
                            }

                            echo "</td>";
                            echo "<td class='btn-btn-container'>";

                            // Actions for "Delivery Note" user type
                            if ($user_type == 'Delivery Note') {
                                if ($row['status'] == 'Completed') {
                                    // Enable print button if status is 'Completed'


                                    echo "<a href = 'print_delivery_note.php?id=" . $row['id'] . "'><button><i class='fas fa-print'></i><div class='btn-title'>Delivery Note</div></button></a>";
                                } else {
                                    // Disable print button if status is not 'Completed'
                                    echo "<a href = 'print_delivery_note.php?id=" . $row['id'] . "'><button disabled style=' background-color:#776ae9; cursor:not-allowed;'><i class='fas fa-print'></i><div class='btn-title'>Delivery Note</div></button></a>";
                                }
                            } else {
                                // Buttons for other users
                                echo "<a href = 'download_pdf.php?id=" . $row['id'] . "'><button><i class='fas fa-eye'></i><div class='btn-title'>View</div></button></a>";
                                echo "<a href = 'print_request.php?id=" . $row['id'] . "'><button><i class='fas fa-print'></i></i><div class='btn-title'>Print</div></button></a>";
                                echo "<a href = 'download_request.php?id=" . $row['id'] . "'><button><i class='fas fa-file-pdf'></i></i></i><div class='btn-title'>Download</div></button></a>";
                            }

                            ?>
                    <?php echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No requests found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-table">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php @include('layout/footer.php'); ?>