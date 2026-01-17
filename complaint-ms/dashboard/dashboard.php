<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch Counts
// Total Complaints
if ($role == 'admin') {
    $sql = "SELECT count(*) as total FROM complaints";
} else {
    $sql = "SELECT count(*) as total FROM complaints WHERE user_id = $user_id";
}
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_complaints = $row['total'];

// Pending
if ($role == 'admin') {
    $sql_pending = "SELECT count(*) as total FROM complaints WHERE status='Pending'";
} else {
    $sql_pending = "SELECT count(*) as total FROM complaints WHERE user_id = $user_id AND status='Pending'";
}
$result_pending = $conn->query($sql_pending);
$row_pending = $result_pending->fetch_assoc();
$total_pending = $row_pending['total'];

// In Progress
if ($role == 'admin') {
    $sql_prog = "SELECT count(*) as total FROM complaints WHERE status='In Progress'";
} else {
    $sql_prog = "SELECT count(*) as total FROM complaints WHERE user_id = $user_id AND status='In Progress'";
}
$result_prog = $conn->query($sql_prog);
$row_prog = $result_prog->fetch_assoc();
$total_prog = $row_prog['total'];

// Resolved
if ($role == 'admin') {
    $sql_res = "SELECT count(*) as total FROM complaints WHERE status='Resolved'";
} else {
    $sql_res = "SELECT count(*) as total FROM complaints WHERE user_id = $user_id AND status='Resolved'";
}
$result_res = $conn->query($sql_res);
$row_res = $result_res->fetch_assoc();
$total_res = $row_res['total'];
// Priority Counts
$priorities = ['Low', 'Medium', 'High'];
$priority_counts = [];

foreach ($priorities as $p) {
    if ($role == 'admin') {
        $sql_p = "SELECT count(*) as total FROM complaints WHERE priority='$p'";
    } else {
        $sql_p = "SELECT count(*) as total FROM complaints WHERE user_id = $user_id AND priority='$p'";
    }
    $res_p = $conn->query($sql_p);
    $priority_counts[$p] = $res_p->fetch_assoc()['total'];
}
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <h2>Dashboard</h2>
                <p>Welcome to your control panel.</p>
            </div>

            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                <div class="dashboard-widget" style="border-left: 5px solid #3498db;">
                    <h3>Total Complaints</h3>
                    <h1 style="color: #3498db;"><?php echo $total_complaints; ?></h1>
                </div>
                <div class="dashboard-widget" style="border-left: 5px solid #f1c40f;">
                    <h3>Pending</h3>
                    <h1 style="color: #f1c40f;"><?php echo $total_pending; ?></h1>
                </div>
                <div class="dashboard-widget" style="border-left: 5px solid #e67e22;">
                    <h3>In Progress</h3>
                    <h1 style="color: #e67e22;"><?php echo $total_prog; ?></h1>
                </div>
                <div class="dashboard-widget" style="border-left: 5px solid #2ecc71;">
                    <h3>Resolved</h3>
                    <h1 style="color: #2ecc71;"><?php echo $total_res; ?></h1>
                </div>
            </div>

            <!-- Charts Section -->
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3>Complaints by Status</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="card" style="flex: 1; min-width: 300px;">
                    <h3>Complaints by Priority</h3>
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'In Progress', 'Resolved'],
            datasets: [{
                data: [<?php echo $total_pending; ?>, <?php echo $total_prog; ?>, <?php echo $total_res; ?>],
                backgroundColor: ['#f1c40f', '#e67e22', '#2ecc71']
            }]
        }
    });

    // Priority Chart
    const ctxPriority = document.getElementById('priorityChart').getContext('2d');
    new Chart(ctxPriority, {
        type: 'bar',
        data: {
            labels: ['Low', 'Medium', 'High'],
            datasets: [{
                label: 'Number of Complaints',
                data: [<?php echo $priority_counts['Low']; ?>, <?php echo $priority_counts['Medium']; ?>, <?php echo $priority_counts['High']; ?>],
                backgroundColor: ['#2ecc71', '#e67e22', '#e74c3c']
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>