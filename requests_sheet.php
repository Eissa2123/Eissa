<?php
// Include required files
include 'fetch_notifications.php';
$conn = new mysqli('localhost', 'root', '', 'company2');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle POST request for updating status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['id'];
    $status = $_POST['status'];

    $sql = "UPDATE purchase_requests SET status='$status', notification_status = 0, notification_type = 'updated' WHERE id='$request_id'";
    if ($conn->query($sql) === TRUE) {
        $message = "Request updated successfully.";
    } else {
        $error = "Error updating request: " . $conn->error;
    }
}

$uploads_dir = "pr_uploads/";
$default_pic = "img/default.png";

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
$query = "SELECT pr.*, d.name AS department, pri.item_name, pri.quantity, pri.unit, pri.description, pri.picture 
          FROM purchase_requests pr 
          JOIN departments d ON pr.department_id = d.id 
          LEFT JOIN purchase_request_items pri ON pr.id = pri.purchase_request_id 
          WHERE 1=1 ";

// Apply filters
$conditions = [];
if (!empty($status_filter)) {
    $conditions[] = "pr.status = '$status_filter'";
}
if (!empty($start_date) && !empty($end_date)) {
    $conditions[] = "pr.odate BETWEEN '$start_date' AND '$end_date'";
}
if (!empty($reference)) {
    $conditions[] = "pr.reference LIKE '%$reference%'";
}

// Modify condition based on user type
if ($user_type == 'Admin Receiver' || $user_type == 'Receivers' || $user_type == 'Delivery Note') {
    // No department filter needed
} else {
    // For other users, add department filter
    $conditions[] = "pr.department_id = '$department_id'";
}

// Add conditions to the query
if (!empty($conditions)) {
    $query .= " AND " . implode(' AND ', $conditions);
}

$query .= " ORDER BY pr.odate DESC LIMIT $offset, $requests_per_page";

// Get the total number of requests for pagination
$count_query = "SELECT COUNT(DISTINCT pr.id) AS total 
                FROM purchase_requests pr 
                JOIN departments d ON pr.department_id = d.id 
                LEFT JOIN purchase_request_items pri ON pr.id = pri.purchase_request_id 
                WHERE 1=1 ";

if (!empty($conditions)) {
    $count_query .= " AND " . implode(' AND ', $conditions);
}

$total_requests_result = $conn->query($count_query);
$total_requests_row = $total_requests_result->fetch_assoc();
$total_requests = $total_requests_row['total'];

// Calculate the total number of pages
$total_pages = ceil($total_requests / $requests_per_page);

// Fetch the requests for the current page
$result = $conn->query($query);

?>

<?php include('layout/header.php'); ?>
<div class="main-container">
    <div class="container">
        <div class="head-container">
            <div class="title-container">
                <h2>Requests List</h2>
            </div>
            <div class="btn-container">
                <a href="list_requests.php">
                    <button class="back">
                        <i class="fas fa-arrow-left"></i>
                        <div class="btn-title">BacK</div>
                    </button>
                </a>
            </div>
        </div>

        <div class="filter-container">
            <div class="input-container">
                <form method="GET" action="">
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
                    <form method="GET" action="export_excel.php">
                        <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                        <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                        <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                        <input type="hidden" name="reference" value="<?php echo $reference; ?>">
                        <button type="submit" name="export" class="export-button">
                            <i class="fas fa-file-excel"></i>
                            <div class="btn-title">Export To Excel</div>
                        </button>
                    </form>
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
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Description</th>
                            <th>Picture</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Initialize variables to track the previous reference and color index
                        $previous_reference = '';
                        $color_index = 0;

                        // Define an array of colors to cycle through
                        $colors = ['#fff', '#e4e7f5']; // Add more colors if needed

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Check if the current reference is the same as the previous one
                                if ($row['reference'] !== $previous_reference) {
                                    // If the reference changes, move to the next color
                                    $color_index = ($color_index + 1) % count($colors);
                                    $previous_reference = $row['reference']; // Update the previous reference
                                }

                                // Get the current color based on the index
                                $row_color = $colors[$color_index];

                                $pic = !empty($row['picture']) && file_exists($uploads_dir . $row['picture'])
                                    ? $uploads_dir . $row['picture']
                                    : $default_pic;

                                echo "<tr style='background-color: $row_color;'>"; // Apply the row color
                                echo "<td data-label = 'Reference:'>" . $row['reference'] . "</td>";
                                echo "<td data-label = 'Date:'>" . $row['odate'] . "</td>";
                                echo "<td data-label = 'Department:'>" . $row['department'] . "</td>";
                                echo "<td data-label = 'Priority:'>";
                                if ($row['priority'] == 'Low') {
                                    echo "<p class='low'>Low</p>";
                                } elseif ($row['priority'] == 'Medium') {
                                    echo "<p class='medium'>Medium</p>";
                                } elseif ($row['priority'] == 'High') {
                                    echo "<p class='high'>High</p>";
                                }
                                echo "</td>";
                                echo "<td data-label = 'Status:'> ";
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
                                echo "</td>";

                                // Display item details
                                echo "<td data-label = 'Item:'>" . $row['item_name'] . "</td>";
                                echo "<td data-label = 'Quantity:'>" . $row['quantity'] . "</td>";
                                echo "<td data-label = 'Unit:'>" . $row['unit'] . "</td>";
                                echo "<td data-label = 'Description:'>" . $row['description'] . "</td>"; ?>
                                <td data-label='Picture:'><img src="<?php echo $pic; ?>" alt="" style="width:70px; height:80px; border-radius:1px;"></td>
                        <?php
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='11'>No requests found</td></tr>";
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
</div>
<?php include('layout/footer.php'); ?>