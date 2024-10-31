<?php
//include 'fetch_notifications.php';
@require('conn/conn.php');

$department_id = $_SESSION['department'];
$username = $_SESSION['username'];
$user_type = $_SESSION['type'];

// Define queries based on user type for home page
if ($user_type === 'Requester') {
    $total_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE department_id='$department_id'")->fetch_assoc()['count'];
    $new_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE department_id='$department_id' AND status='New'")->fetch_assoc()['count'];
    $waiting_approval_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE department_id='$department_id' AND status='Waiting Approval'")->fetch_assoc()['count'];
    $on_progress_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE department_id='$department_id' AND status='On Progress'")->fetch_assoc()['count'];
    $delivery_on_progress_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE department_id='$department_id' AND status='Delivery On Progress'")->fetch_assoc()['count'];
    $completed_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE department_id='$department_id' AND status='Completed'")->fetch_assoc()['count'];
} else {
    // Admin or Receivers: Fetch counts for all departments
    $total_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests ")->fetch_assoc()['count'];
    $new_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE  status='New'")->fetch_assoc()['count'];
    $waiting_approval_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE status='Waiting Approval'")->fetch_assoc()['count'];
    $on_progress_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE  status='On Progress'")->fetch_assoc()['count'];
    $delivery_on_progress_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE  status='Delivery On Progress'")->fetch_assoc()['count'];
    $completed_requests = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE status='Completed'")->fetch_assoc()['count'];
}


// Calculate percentages
$new_percentage = $total_requests > 0 ? ($new_requests / $total_requests) * 100 : 0;
$waiting_approval_percentage = $total_requests > 0 ? ($waiting_approval_requests / $total_requests) * 100 : 0;
$on_progress_percentage = $total_requests > 0 ? ($on_progress_requests / $total_requests) * 100 : 0;
$delivery_on_progress_percentage = $total_requests > 0 ? ($delivery_on_progress_requests / $total_requests) * 100 : 0;
$completed_percentage = $total_requests > 0 ? ($completed_requests / $total_requests) * 100 : 0;



// Fetch priority data based on user type
$priority_counts = [];
$priorities = ['High', 'Medium', 'Low'];

if ($user_type === 'Admin Receiver' || $user_type === 'Receivers') {
    // Show priorities for all requests
    foreach ($priorities as $priority) {
        $count = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE priority='$priority'")->fetch_assoc()['count'];
        $priority_counts[$priority] = $count;
    }
} else {
    // Show priorities based on department ID
    foreach ($priorities as $priority) {
        $count = $conn->query("SELECT COUNT(*) as count FROM purchase_requests WHERE department_id='$department_id' AND priority='$priority'")->fetch_assoc()['count'];
        $priority_counts[$priority] = $count;
    }
}



// Fetch data for department-level charts (from charts.php)
if ($_SESSION['type'] == 'Admin Receiver' || $_SESSION['type'] == 'Receiver') {
    $departmentsQuery = "
        SELECT 
            departments.name AS department_name, 
            COUNT(purchase_requests.id) AS total_requests, 
            SUM(CASE WHEN purchase_requests.status = 'Completed' THEN 1 ELSE 0 END) AS completed_requests
        FROM purchase_requests 
        JOIN departments ON purchase_requests.department_id = departments.id
        GROUP BY departments.id";
    $departmentsResult = $conn->query($departmentsQuery);

    $departmentsData = [];
    while ($row = $departmentsResult->fetch_assoc()) {
        $departmentsData[] = $row;
    }
} else {
    $departmentId = $_SESSION['department'];
    $userDeptQuery = "
        SELECT 
            COUNT(*) AS total_requests, 
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_requests 
        FROM purchase_requests 
        WHERE department_id = $departmentId";
    $userDeptResult = $conn->query($userDeptQuery);
    $userDeptData = $userDeptResult->fetch_assoc();
}

?>

<style>

</style>

<?php include("layout/header.php"); ?>

<main class="main-container">
    <div class="home-container">

        <div class="card1">
            <div class="title">
                <span class="material-symbols-outlined">list_alt</span>
                <p class="title-text">Total Request</p>
            </div>
            <div class="data">
                <p><?php echo $total_requests; ?></p>
            </div>
        </div>

        <div class="card2">
            <div class="title">
                <span class="material-symbols-outlined">hourglass_empty</span>

                <p class="title-text">Waiting Approval</p>
            </div>
            <div class="data">
                <p><?php echo $waiting_approval_requests; ?></p>
            </div>
            <p class="percent">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1792 1792" fill="currentColor" height="20" width="20">
                    <path d="M1408 1216q0 26-19 45t-45 19h-896q-26 0-45-19t-19-45 19-45l448-448q19-19 45-19t45 19l448 448q19 19 19 45z">
                    </path>
                </svg> <?php echo number_format($waiting_approval_percentage, 2); ?>%
            </p>
        </div>

        <div class="card3">
            <div class="title">
                <span class="material-symbols-outlined">quick_reorder</span>
                <p class="title-text">Delivery On Progress</p>
            </div>
            <div class="data">
                <p><?php echo $delivery_on_progress_requests; ?></p>
            </div>

            <p class="percent">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1792 1792" fill="currentColor" height="20" width="20">
                    <path d="M1408 1216q0 26-19 45t-45 19h-896q-26 0-45-19t-19-45 19-45l448-448q19-19 45-19t45 19l448 448q19 19 19 45z">
                    </path>
                </svg> <?php echo number_format($delivery_on_progress_percentage, 2); ?>%
            </p>
        </div>

        <div class="card4">
            <div class="title">
                <span class="material-symbols-outlined">fiber_new</span>
                <p class="title-text">New</p>
            </div>

            <div class="data">
                <p><?php echo $new_requests; ?></p>
            </div>

            <p class="percent">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1792 1792" fill="currentColor" height="20" width="20">
                    <path d="M1408 1216q0 26-19 45t-45 19h-896q-26 0-45-19t-19-45 19-45l448-448q19-19 45-19t45 19l448 448q19 19 19 45z">
                    </path>
                </svg> <?php echo number_format($new_percentage, 2); ?>%
            </p>
        </div>

        <div class="card5">
            <div class="title">
                <span class="material-symbols-outlined">progress_activity</span>
                <p class="title-text">On Progress</p>

            </div>

            <div class="data">
                <p><?php echo $on_progress_requests; ?></p>
            </div>

            <p class="percent">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1792 1792" fill="currentColor" height="20" width="20">
                    <path d="M1408 1216q0 26-19 45t-45 19h-896q-26 0-45-19t-19-45 19-45l448-448q19-19 45-19t45 19l448 448q19 19 19 45z">
                    </path>
                </svg> <?php echo number_format($on_progress_percentage, 2); ?>%
            </p>
        </div>

        <div class="card6">
            <div class="title">
                <span class="material-symbols-outlined">verified_user</span>
                <p class="title-text">Completed</p>

            </div>
            <div class="data">
                <p><?php echo $completed_requests; ?></p>
            </div>

            <div class="percent">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1792 1792" fill="currentColor" height="20" width="20">
                    <path d="M1408 1216q0 26-19 45t-45 19h-896q-26 0-45-19t-19-45 19-45l448-448q19-19 45-19t45 19l448 448q19 19 19 45z">
                    </path>
                </svg> <?php echo number_format($completed_percentage, 2); ?>%
            </div>
        </div>


        <div class="card7">
            <div class="chart">
                <p>Status</p><canvas id="categoryChart"></canvas>
            </div>
        </div>

     
        <div class="card8">
            <div class="chart">
                <p>Status</p><canvas id="statusChart"></canvas>
            </div>

            


        </div>

        <div class="card13">
        <div class="priority">
                <p>Requests by Priority</p>
                <?php foreach ($priority_counts as $priority => $count):
                    $priority_class = strtolower($priority);  // Convert priority to lowercase for class naming
                ?>
                    <div class="priority-item priority-<?php echo $priority_class; ?>">
                        <div class="priority-title">
                            <span class="priority-circle"></span><?php echo $priority; ?>
                        </div>
                        <div class="priority-count">
                            <?php echo $count; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>



        <?php
        if ($_SESSION['type'] == 'Admin Receiver' || $_SESSION['type'] == 'Receiver') {
        ?>
            <div class="card10">
                <p>Status</p>
                <canvas id="departmentTotalRequestsChart" class="chart"></canvas>
            </div>

            <div class="card11">
                <p>Status</p>
                <canvas id="departmentRequestsComparisonChart" class="chart"></canvas>
            </div>


        <?php
        } else {
        ?>
            <div class="card12">
                <p>Status</p>
                <canvas id="userDeptRequestsComparisonChart" class="chart"></canvas>
            </div>
        <?php
        }
        ?>
    </div>


</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Donut Chart with Custom Style
    var ctxStatus = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(ctxStatus, {
        type: 'doughnut', // Changed to doughnut chart to resemble a donut
        data: {
            labels: ['New', 'Waiting Approval', 'On Progress', 'Delivery On Progress', 'Completed'],
            datasets: [{
                label: 'Request Status',
                data: [
                    <?php echo $new_requests; ?>,
                    <?php echo $waiting_approval_requests; ?>,
                    <?php echo $on_progress_requests; ?>,
                    <?php echo $delivery_on_progress_requests; ?>,
                    <?php echo $completed_requests; ?>
                ],
                backgroundColor: ['#8170f3', '#bfb6ff', '#eceafe', '#818289', '#464548'], // Soft pastel colors
                hoverOffset: 2, // Increase the offset when hovered for a better effect
                borderWidth: 0, // No borders for a clean look
            }]
        },
        options: {
            cutout: '80%', // Creates a larger hole for a donut-like effect
            responsive: true,
            plugins: {
                legend: {
                    display: false // Minimal design without the legend
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw; // Tooltip formatting
                        }
                    }
                }
            }
        }
    });


    // Category Chart
    fetch('fetch_category_data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Bar Chart with Rounded Corners and Custom Style
            var ctxCategory = document.getElementById('categoryChart').getContext('2d');
            var categoryChart = new Chart(ctxCategory, {
                type: 'bar',
                data: {
                    labels: data.labels, // These are your dynamic labels
                    datasets: [{
                        label: 'Requests by Category',
                        data: data.counts, // Your dynamic data
                        backgroundColor: ['#836eff', '#beb8ff', '#eceafe', '#818289', '#464548'], // Soft colors for bars
                        // Note: 'borderRadius' is not directly available for bar datasets in Chart.js
                        // You may need to use a plugin for rounded corners
                        barThickness: 30 // Set the thickness of each bar to be narrower
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false // Hide legend for a minimal design
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false, // Hide grid lines
                            },
                            ticks: {
                                font: {
                                    size: 14 // Font size for ticks
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false, // Hide grid lines for X-axis
                            },
                            ticks: {
                                font: {
                                    size: 14 // Font size for ticks
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('There has been a problem with your fetch operation:', error);
        });




    // Department Chart (Admin Receiver and Receivers only)
    <?php if ($_SESSION['type'] == 'Admin Receiver' || $_SESSION['type'] == 'Receiver') { ?>
        var departmentLabels = <?php echo json_encode(array_column($departmentsData, 'department_name')); ?>;
        var totalRequestsData = <?php echo json_encode(array_column($departmentsData, 'total_requests')); ?>;
        var completedRequestsData = <?php echo json_encode(array_column($departmentsData, 'completed_requests')); ?>;
        new Chart(document.getElementById('departmentTotalRequestsChart'), {
            type: 'line',
            data: {
                labels: departmentLabels,
                datasets: [{
                    label: 'Total Requests',
                    data: totalRequestsData,
                    borderColor: 'rgba(131, 111, 255, 1)',
                    backgroundColor: 'rgba(234, 233, 251, 0.2)', // Light fill under the curve
                    fill: true, // Fill area under the line to create the wave effect
                    tension: 0.4, // Smooth curve (wave) effect
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                elements: {
                    line: {
                        borderWidth: 2 // Thicker line for visibility
                    }
                }
            }
        });

        // Wave chart for comparison of total and completed requests by department
        new Chart(document.getElementById('departmentRequestsComparisonChart'), {
            type: 'line',
            data: {
                labels: departmentLabels,
                datasets: [{
                        label: 'Total Requests',
                        data: totalRequestsData,
                        borderColor: 'rgba(131, 111, 255, 1)',
                        backgroundColor: 'rgba(234, 233, 251, 0.5)', // Light fill under the curve
                        fill: true,
                        tension: 0.4, // Smooth curve (wave) effect
                    },
                    {
                        label: 'Completed Requests',
                        data: completedRequestsData,
                        borderColor: 'rgba(130, 130, 140, 1)',
                        backgroundColor: 'rgba(212, 210, 220, 0.5)', // Light fill under the curve
                        fill: true,
                        tension: 0.4, // Smooth curve (wave) effect
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                elements: {
                    line: {
                        borderWidth: 2 // Thicker line for visibility
                    }
                }
            }
        });
    <?php } else { ?>
        // Chart for a specific department (for non-admin users)
        new Chart(document.getElementById('userDeptRequestsComparisonChart'), {
            type: 'line',
            data: {
                labels: ['Total Requests', 'Completed Requests'],
                datasets: [{
                    label: 'Your Department',
                    data: [<?php echo $userDeptData['total_requests']; ?>, <?php echo $userDeptData['completed_requests']; ?>],
                    borderColor: 'rgba(131, 111, 255, 1)',
                    backgroundColor: 'rgba(234, 233, 251, 0.5)', // Light fill under the curve
                    fill: true,
                    tension: 0.4, // Smooth curve (wave) effect
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                elements: {
                    line: {
                        borderWidth: 2 // Thicker line for visibility
                    }
                }
            }
        });
    <?php } ?>
</script>

<?php include('layout/footer.php'); ?>