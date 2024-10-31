<?php
include 'fetch_notifications.php';

@require('conn/conn.php');

// Fetch total requests and completed requests for each department (for Admin Receiver and Receivers)
$departmentsQuery = "
    SELECT 
        departments.name AS department_name, 
        COUNT(purchase_requests.id) AS total_requests, 
        SUM(CASE WHEN purchase_requests.status = 'completed' THEN 1 ELSE 0 END) AS completed_requests
    FROM purchase_requests 
    JOIN departments ON purchase_requests.department_id = departments.id
    GROUP BY departments.id";
$departmentsResult = $conn->query($departmentsQuery);

// Fetch total requests and completed requests for the specific user's department (for other users)
$departmentId = $_SESSION['department']; // Assuming session stores department_id
$userDeptQuery = "
    SELECT 
        COUNT(*) AS total_requests, 
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_requests 
    FROM purchase_requests 
    WHERE department_id = $departmentId";
$userDeptResult = $conn->query($userDeptQuery);

// Prepare data for Chart.js
$departmentsData = [];
while ($row = $departmentsResult->fetch_assoc()) {
    $departmentsData[] = $row;
}

$userDeptData = $userDeptResult->fetch_assoc();

$user_type = $_SESSION['type'];

if ($_SESSION['type'] == 'Admin Receiver' || $_SESSION['type'] == 'Receiver') {
?>


    
        <div class="chart">
            <p>Status</p>
            <canvas id="departmentTotalRequestsChart" class="chart"></canvas>
        </div>
    

    
        <div class="chart">
            <p>Status</p>
            <canvas id="departmentRequestsComparisonChart" class="chart"></canvas>
        </div>
    

<?php
} else {
?>

    
        <div class="chart">
            <p>Status</p>
            <canvas id="userDeptRequestsComparisonChart" class="chart"></canvas>
        </div>

   

<?php
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Prepare data for total requests per department (for Admin Receiver and Receivers)
    let departmentLabels = [];
    let totalRequestsData = [];
    let completedRequestsData = [];

    <?php foreach ($departmentsData as $data) { ?>
        departmentLabels.push('<?php echo $data['department_name']; ?>');
        totalRequestsData.push(<?php echo $data['total_requests']; ?>);
        completedRequestsData.push(<?php echo $data['completed_requests']; ?>);
    <?php } ?>

    // Prepare data for the specific department of the logged-in user (for Other Users)
    let userDeptTotalRequests = <?php echo $userDeptData['total_requests']; ?>;
    let userDeptCompletedRequests = <?php echo $userDeptData['completed_requests']; ?>;
</script>



<script>
    // Check if the user is an Admin Receiver or Receiver
    <?php if ($_SESSION['type'] == 'Admin Receiver' || $_SESSION['type'] == 'Receivers') { ?>
        // Chart for total requests per department
        new Chart(document.getElementById('departmentTotalRequestsChart'), {
            type: 'line',
            data: {
                labels: departmentLabels,
                datasets: [{
                    label: 'Total Requests',
                    data: totalRequestsData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false,
                }]
            },
        });

        // Chart for total requests vs. completed requests per department
        new Chart(document.getElementById('departmentRequestsComparisonChart'), {
            type: 'line',
            data: {
                labels: departmentLabels,
                datasets: [{
                        label: 'Total Requests',
                        data: totalRequestsData,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        fill: false,
                    },
                    {
                        label: 'Completed Requests',
                        data: completedRequestsData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        fill: false,
                    }
                ]
            },
        });
    <?php } else { ?>
        // Chart for total requests vs. completed requests for the specific user's department
        new Chart(document.getElementById('userDeptRequestsComparisonChart'), {
            type: 'line',
            data: {
                labels: ['Total Requests', 'Completed Requests'],
                datasets: [{
                    label: 'Your Department',
                    data: [userDeptTotalRequests, userDeptCompletedRequests],
                    borderColor: 'rgba(153, 102, 255, 1)',
                    fill: false,
                }]
            },
        });
    <?php } ?>
</script>