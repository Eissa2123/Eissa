<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'company');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$purchase_request_id = $_GET['id'];

// Fetch purchase request details
$sql = "SELECT pr.*, d.name AS department, c.category AS category 
        FROM purchase_requests pr 
        JOIN departments d ON pr.department_id = d.id 
        JOIN categories c ON pr.category = c.id 
        WHERE pr.id='$purchase_request_id'";
$request_result = $conn->query($sql);
$request = $request_result->fetch_assoc();

// Fetch purchase request items
$sql = "SELECT * FROM purchase_request_items WHERE purchase_request_id='$purchase_request_id'";
$items_result = $conn->query($sql);

$uploads_dir = "pr_uploads/";
$default_pic = "img/default.png";

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assist/invoice.css">
</head>

<body>

    <div class="invoice-wrapper" id="print-area">
        <div class="invoice">
            <div class="invoice-container">

                <div class="invoice-head">

                    <div class="invoice-head-top">
                        <div class="invoice-head-top-left text-start">
                            <img src="img/logo.png">
                        </div>
                        <div class="invoice-head-top-right text-end">
                            <h3>Purchase Request</h3>
                        </div>
                    </div>
                    <div class="hr"></div>

                    <div class="invoice-head-middle">
                        <div class="invoice-head-middle-left text-start">
                            <p><span class="text-bold">Date:</span> <?php echo htmlspecialchars($request['odate']); ?></p>
                        </div>
                        <div class="invoice-head-middle-right text-end">
                            <p><span class="text-bold">PR No:</span> <?php echo htmlspecialchars($request['reference']); ?></p>
                        </div>
                    </div>
                    <div class="hr"></div>
                    <div class="invoice-head-bottom">
                        <div class="invoice-head-bottom-left">
                            <ul>
                                <li class='text-bold'>Requestes Info:</li>
                                <p><span class="text-bold">Department:</span> <?php echo htmlspecialchars($request['department']); ?></p>
                                <p><span class="text-bold">Category:</span> <?php echo htmlspecialchars($request['category']); ?></p>
                                <p><span class="text-bold">Priority:</span> <?php echo htmlspecialchars($request['priority']); ?></p>
                            </ul>
                        </div>
                        <div class="invoice-head-bottom-right">
                            <ul class="text-end">
                                <li class='text-bold'>Send To:</li>
                                <li><?php echo htmlspecialchars($request['sent_to']); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="hr"></div>
                    <br>
                </div>

                <div class="overflow-view">
                    <div class="invoice-body">
                        <table>
                            <thead>
                                <tr>
                                    <td class="text-bold">Item</td>
                                    <td class="text-bold">Quantity</td>
                                    <td class="text-bold">Unit</td>
                                    <td class="text-bold">Description</td>
                                    <td class="text-bold">Picture</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items_result->fetch_assoc()) {
                                    $pic = !empty($item['picture']) && file_exists($uploads_dir . $item['picture'])
                                        ? $uploads_dir . $item['picture']
                                        : $default_pic;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td><img src="<?php echo $pic; ?>" alt="" style="width:80px;"></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="invoice-body-bottom">
                            <div class="invoice-body-info-item border-bottom">
                                <div class="info-item-td text-end text-bold">Requester:</div>
                                <div class="info-item-td text-end text-bold">Manager Approval:</div>
                                <div class="info-item-td text-end text-bold">GM Approval:</div>
                                <div class="info-item-td text-end text-bold">PIO Director:</div>
                            </div>
                            <div class="invoice-body-info-item border-bottom">
                                <div class="info-item-td text-end text-bold"><br></div>
                                <div class="info-item-td text-end text-bold"><br></div>
                                <div class="info-item-td text-end text-bold"><br></div>
                                <div class="info-item-td text-end text-bold"><br></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="invoice-foot text-center">
                    <p><span class="text-bold text-center">NOTE: </span> </p>
                    <p>1. Make sure all the data required are completed in the form before printing.</p>
                    <p>2. Forms submitted without all required approvals will be returned resulting in processing delays.</p>
                    <p>3. The order will take a maximum of 1 week to deliver (depending on availability of the requested items and of our Purchaser).</p>
                    <p>4. In case if the Purchase request going through CPCD the availability of item arrive time depend on after arrange of quotations & get all approvals through JSAP system then deliver of vendor.</p>
                </div>
                    <div class="invoice-btns">
                        <button type="button" class="invoice-btn" onclick="printInvoice()">
                            <span>
                                <i class="fa-solid fa-print"></i>
                            </span>
                            <span>Print</span>
                        </button>
                        
                        <button type="button" class="invoice-btn">
                            <span>
                                <i class="fa-solid fa-download"></i>
                            </span>
                            <span>Download</span>
                        </button>

                        <button type="button" class="invoice-btn " onclick="eissa()">
                        <span>
                                <i class="fa-solid fa-download"></i>
                            </span>
                            <span>Back</span>
                        </button>
                        <script>
                            function eissa() {
                                window.location.href = 'list_requests.php?id=<?php echo $request['id']; ?>';
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script/invoice.js"></script>
</body>

</html>