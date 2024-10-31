<?php
require('conn/conn.php');


// Initialize the reference variable
$reference = '';


// Fetch user information
$username = $_SESSION['username'];
$user_sql = "SELECT u.department, d.name AS department, d.id as department_id 
             FROM users u 
             JOIN departments d ON u.department = d.id 
             WHERE u.username='$username'";
$user_result = $conn->query($user_sql);

if ($user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
    $department_id = $user_row['department_id'];
    $department = $user_row['department'];


    // Generate a unique 3-digit number combined with the department number
    $random_number = mt_rand(100, 999);
    $reference = $random_number . '-' . str_pad($department_id, 3, '0', STR_PAD_LEFT);
} else {

    exit();
}

// Get categories
$category_sql = "SELECT * FROM categories";
$category_result = $conn->query($category_sql);


// Get receivers
$type_sql = "SELECT * FROM users WHERE type IN ('Admin Receiver', 'Receivers')";
$type_result = $conn->query($type_sql);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $sent_to = $_POST['sent_to'];
    $items = $_POST['items'];


    //$reference = uniqid('PR-');



    // Insert the purchase request
    $sql = "INSERT INTO purchase_requests (reference, department_id, category, priority, sent_to, notification_status, notification_type) 
            VALUES ('$reference', '$department_id', '$category', '$priority', '$sent_to', 0, 'new')";
    $conn->query($sql);
    $purchase_request_id = $conn->insert_id;

    // Directory for file uploads
    $target_dir = "pr_uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Process each item
    foreach ($items as $key => $item) {
        $item_name = $item['item_name'];
        $unit = $item['unit'];
        $quantity = $item['quantity'];
        $description = $item['description'];
        $picture = '';

        // Handle file upload
        if (isset($_FILES['items']['name'][$key]['picture']) && $_FILES['items']['error'][$key]['picture'] == 0) {
            $picture_name = basename($_FILES['items']['name'][$key]['picture']);
            $target_file = $target_dir . $picture_name;

            if (move_uploaded_file($_FILES['items']['tmp_name'][$key]['picture'], $target_file)) {
                //$picture = $target_file;  // Save the file path to store in the database
                $picture = basename($_FILES['items']['name'][$key]['picture']);
            } else {
                echo "Error uploading file for item: $item_name";
            }
        }

        // Insert item into database
        $sql = "INSERT INTO purchase_request_items (purchase_request_id, item_name, unit, quantity, description, picture) 
                VALUES ('$purchase_request_id', '$item_name', '$unit', '$quantity', '$description', '$picture')";
        $conn->query($sql);
    }

    // Redirect or confirmation
    header("Location: list_requests.php");
    exit();
}
?>



<?php @include('layout/header.php'); ?>

<div class="main-container">
    <div class="container">
        <div class="head-container">
            <div class="title-container">
                <h2>Purchase Request</h2>
            </div>
            <div class="btn-container">
                <a href="home.php">
                    <button class="back">
                        <i class="fas fa-arrow-left"></i>
                        <div class="btn-title">BacK</div>
                    </button>
                </a>
            </div>
        </div>


        <form method="post" action="" enctype="multipart/form-data">
            <div class="filter-container">
                <div class="input-container">
                    <label for="reference">Reference:</label>
                    <input type="text" id="reference" name="reference" value="<?php echo htmlspecialchars($reference ?? ''); ?>" readonly>
                </div>
                <div class="input-container">
                    <label for="department">Department:</label>
                    <input type="text" id="department" name="department" value="<?php echo $department; ?>" readonly>
                </div>
                <div class="input-container">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>

                        <?php
                        while ($category_row = $category_result->fetch_assoc()) : ?>
                            <option value="<?php echo $category_row['id']; ?>"><?php echo $category_row['category']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="input-container">
                    <label for="priority">Priority:</label>
                    <select id="priority" name="priority" required>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="input-container">
                    <label for="sent_to">Sent To:</label>
                    <select id="sent_to" name="sent_to" required>
                        <?php
                        while ($type_row = $type_result->fetch_assoc()) : ?>
                            <option value="<?php echo $type_row['id']; ?>"><?php echo $type_row['username']; ?></option>
                        <?php endwhile;
                        ?>
                    </select>
                </div>
            </div>



            <div class="table-container">
                <div class="head-table">
                </div>
                <hr>
                <div class="body-table">
                    <table id="items_table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Description</th>
                                <th>Picture</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td data-label='Item:'><input type="text" name="items[0][item_name]" required></td>
                                <td data-label='Quantity:'><input type="number" name="items[0][quantity]" required></td>
                                <td data-label='Unit:'>
                                    <select name="items[0][unit]" required>
                                        <option value="Pcs">Pcs</option>
                                        <option value="Box">Box</option>
                                        <option value="Meter">Meter</option>
                                        <option value="Roll">Roll</option>
                                        <option value="Set">Set</option>
                                        <option value="Packed">Packed</option>
                                        <option value="Nose">Nose</option>
                                        <option value="Cartoon">Cartoon</option>
                                        <option value="Kilo">Kilo</option>
                                        <option value="Strip">Strip</option>
                                        <option value="Gallon">Gallon</option>
                                        <option value="Sheet">Sheet</option>
                                    </select>
                                </td>
                                <td data-label='Description:'><input type="text" name="items[0][description]" required></td>
                                <td data-label='Picture:'><input type="file" name="items[0][picture]"></td>
                                <td data-label='' class="btn-container"><button type="button" onclick="addItemRow()">
                                        <i class="fas fa-plus"></i>
                                        <div class="btn-title">New Item</div>
                                    </button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="submit-btn">
                        <button>
                            <input type="submit">
                            <div class="btn-title">Save</div>
                        </button>
                    </div>
                </div>
            </div>
        </form>




        <script>
            let itemIndex = 1;

            function addItemRow() {
                const table = document.getElementById('items_table');
                const row = table.insertRow();
                row.innerHTML = `
        <td data-label='Item:'><input type="text" name="items[${itemIndex}][item_name]" required></td>
        <td data-label='Quantity:'><input type="number" name="items[${itemIndex}][quantity]" required></td>
                <td data-label='Unit:'>
                    <select name="items[${itemIndex}][unit]" required>
                        <option value="Pcs">Pcs</option>
                        <option value="Box">Box</option>
                        <option value="Meter">Meter</option>
                        <option value="Roll">Roll</option>
                        <option value="Set">Set</option>
                        <option value="Packed">Packed</option>
                        <option value="Nose">Nose</option>
                        <option value="Cartoon">Cartoon</option>
                        <option value="Kilo">Kilo</option>
                        <option value="Strip">Strip</option>
                        <option value="Gallon">Gallon</option>
                        <option value="Sheet">Sheet</option>
                    </select>
                </td>
        <td data-label='Description:'><input type="text" name="items[${itemIndex}][description]" required></td>
        <td data-label='Picture:'><input type="file" name="items[${itemIndex}][picture]" accept="image/*"></td>
        <td data-label='' class="btn-container"><button type="button" onclick="removeItemRow(this)">
            <i class="fas fa-minus"></i>
            <div class="btn-title">Delete Item</div>
          </button></td>
             `;
                itemIndex++;
            }

            function removeItemRow(button) {
                const row = button.parentElement.parentElement;
                row.remove();
            }
        </script>
    </div>
</div>
<?php @include('layout/footer.php'); ?>