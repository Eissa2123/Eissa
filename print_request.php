<?php
require 'dompdf/autoload.inc.php';
require 'conn/conn.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Initialize DomPDF options
$options = new Options();
$options->set('isRemoteEnabled', true); // Enable remote resources like images
$dompdf = new Dompdf($options);

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

// Convert logo to Base64
$logoPath = "img/logo.png";
if (file_exists($logoPath)) {
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoSrc = 'data:image/png;base64,' . $logoData;
} else {
    $logoSrc = '';  // Fallback if the logo is not found
}

// Start DOMPDF
$dompdf = new Dompdf();
ob_start();
?>

<!DOCTYPE html>
<html>
<!DOCTYPE html>
<html>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <style>
        *,
        *::after,
        *::before {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        :root {
            --blue-color: #232d72;
            --dark-color: #535b61;
            --white-color: #fff;
            --purpol-color: #5c6ac4;
            --info-color: #f1f5f9;
        }

        ul {
            list-style-type: none;
        }

        ul li {
            margin: 2px 0;
        }

        /* text colors */
        .text-dark {
            color: var(--dark-color);
        }

        .text-blue {
            color: var(--blue-color);
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-start {
            text-align: left;
        }

        .text-bold {
            font-weight: 700;
        }

        /* hr line */
        .hr {
            height: 1px;
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* border-bottom */
        .border-bottom {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-color);
            font-size: 10px;
        }

        .invoice-wrapper {
            min-height: 100vh;
            background-color: rgba(0, 0, 0, 0.1);
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .invoice {
            max-width: 850px;
            margin-right: auto;
            margin-left: auto;
            background-color: var(--white-color);
            padding: 30px;
            border: 1px solid rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            min-height: 920px;
        }

        .invoice-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }

        .invoice-head-top {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .invoice-head-top-left img {
            width: 200px;
            padding-bottom: -50px;
        }

        .invoice-head-top-left h3 {
            font-weight: 600;
            font-size: 20px;
            color: var(--blue-color);
        }

        .invoice-head-top-right h3 {
            font-weight: 600;
            font-size: 18px;
            color: var(--blue-color);
            margin-top:-25px;
        }

        .invoice-head-middle,
        .invoice-head-bottom {
            background-color: #f1f5f9;
            padding: 0px 10px;
        }

        .invoice-body {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            overflow: hidden;
        }

        .invoice-body table {
            border-collapse: collapse;
            border-radius: 6px;
            width: 100%;
            text-align: center;
        }

        .invoice-body table td,
        .invoice-body table th {
            padding: 5px;
        }

        .invoice-body table tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .invoice-body table thead {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .invoice-body-info-item {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            padding-bottom: 0px;
            gap: 2px;
        }

        .invoice-body-info-item .info-item-td {
            text-align: center;
            padding: 12px;
            background-color: rgba(0, 0, 0, 0.02);
        }

        .invoice-foot {
            padding: 10px 0;
        }

        .invoice-foot p {
            text-align: left;
            font-size: 7px;
        }

        .invoice-head-top,
        .invoice-head-middle,
        .invoice-head-bottom {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            padding-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="invoice">
            <div class="invoice-container">
                <div class="invoice-head">
                    <div class="invoice-head-top">
                        <div class="invoice-head-top-left text-start">
                            <img src="<?php echo $logoSrc; ?>" alt="Company Logo">
                        </div>
                        <div class="invoice-head-top-right text-end">
                            <h3>Purchase Request</h3>
                        </div>
                    </div>
                    <div class="hr"></div>

                    <div class="invoice-head-middle">
                        <div class="invoice-head-middle-left text-start">
                            <p><span class="text-bold">Date:</span> <?php echo htmlspecialchars($request['odate']); ?></p>
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
                                <p><span class="text-bold">Send To:</span> <?php echo htmlspecialchars($request['sent_to']); ?></p>
                            </ul>
                        </div>
                    </div>
                    <div class="hr" style="margin-bottom: 5px;"></div>


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
                                    // Get the image path or fallback to the default picture
                                    $pic_path = !empty($item['picture']) && file_exists($uploads_dir . $item['picture'])
                                        ? $uploads_dir . $item['picture']
                                        : $default_pic;

                                    // Convert the image to Base64
                                    $pic_data = base64_encode(file_get_contents($pic_path));
                                    $pic_src = 'data:image/png;base64,' . $pic_data;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td><img src="<?php echo $pic_src; ?>" alt="" style="height:40px;"></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <table>
                            <thead>
                                <tr>
                                    <td class="text-bold">Requester</td>
                                    <td class="text-bold">Manager Approval</td>
                                    <td class="text-bold">GM Approval</td>
                                    <td class="text-bold">PIO Director</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-bold"><br></td>
                                    <td class="text-bold"> </td>
                                    <td class="text-bold"> </td>
                                    <td class="text-bold"> </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
                <div class="invoice-foot text-center">
                    <p><span class="text-bold text-center">NOTE: </span> </p>
                    <p>1. Make sure all the data required are completed in the form before printing.</p>
                    <p>2. Forms submitted without all required approvals will be returned resulting in processing delays.</p>
                    <p>3. The order will take a maximum of 1 week to deliver (depending on availability of the requested items and of our Purchaser).</p>
                    <p>4. In case if the Purchase request going through CPCD the availability of item arrive time depend on after arrange of quotations & get all approvals through JSAP system then deliver of vendor.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Display the PDF in the browser
$dompdf->stream("purchase_request_$purchase_request_id.pdf", ["Attachment" => false, "Destination" => "new_window"]);
?>