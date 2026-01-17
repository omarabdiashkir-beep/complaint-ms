<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle Delete (Admin only)
if ($role == 'admin' && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM complaints WHERE id = $delete_id");
    header("Location: complaints.php?msg=deleted");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <h2>
                        <?php echo ($role == 'admin') ? 'Manage Complaints' : 'My Complaints'; ?>
                    </h2>
                    <div>
                        <a href="export_complaints.php" class="btn btn-secondary" style="background:#28a745; margin-right: 10px;">Export CSV</a>
                        <?php if ($role == 'student'): ?>
                            <a href="add_complaint.php" class="btn btn-primary">Lodge Complaint</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Search Form -->
                <div style="margin-top: 20px;">
                    <form action="" method="GET" style="display: flex; gap: 10px;">
                        <input type="text" id="live_search" name="search" class="form-control"
                            placeholder="Live Search by ID, Title, Status..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success">Complaint deleted successfully.</div>
            <?php endif; ?>

            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <?php if ($role == 'admin')
                                echo "<th>Student</th>"; ?>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Base Query
                        $sql = "SELECT c.*, cat.name as category_name, u.first_name, u.last_name 
                                FROM complaints c 
                                JOIN categories cat ON c.category_id = cat.id 
                                JOIN users u ON c.user_id = u.id";

                        $where_clauses = [];

                        // Role Filter
                        if ($role != 'admin') {
                            $where_clauses[] = "c.user_id = $user_id";
                        }

                        // Search Filter
                        if (!empty($search)) {
                            $escaped_search = $conn->real_escape_string($search);
                            $term = "%$escaped_search%";
                            $where_clauses[] = "(c.id LIKE '$term' OR c.title LIKE '$term' OR c.status LIKE '$term' OR cat.name LIKE '$term' OR u.first_name LIKE '$term' OR u.last_name LIKE '$term')";
                        }

                        if (count($where_clauses) > 0) {
                            $sql .= " WHERE " . implode(' AND ', $where_clauses);
                        }

                        $sql .= " ORDER BY c.created_at ASC";

                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Status badge colors
                                $status_text_color = '#333';
                                $status_bg = '#eee';

                                if ($row['status'] == 'Pending') {
                                    $status_bg = '#ffc107';
                                    $status_text_color = '#000';
                                }
                                if ($row['status'] == 'In Progress') {
                                    $status_bg = '#fd7e14';
                                    $status_text_color = '#fff';
                                }
                                if ($row['status'] == 'Resolved') {
                                    $status_bg = '#28a745';
                                    $status_text_color = '#fff';
                                }

                                $priority_color = 'green';
                                if ($row['priority'] == 'Medium')
                                    $priority_color = 'orange';
                                if ($row['priority'] == 'High')
                                    $priority_color = 'red';

                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
                                if ($role == 'admin') {
                                    echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                                }
                                echo "<td><span style='color: $priority_color; font-weight:bold;'>" . $row['priority'] . "</span></td>";
                                echo "<td><span class='badge' style='background-color: $status_bg; color: $status_text_color;'>" . $row['status'] . "</span></td>";
                                echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                echo "<td>
                                        <a href='view_complaint.php?id=" . $row['id'] . "' class='btn btn-primary' style='padding: 5px 10px; font-size: 0.9rem;'>View</a>";
                                if ($role == 'admin') {
                                    echo " <a href='complaints.php?delete_id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")' style='padding: 5px 10px; font-size: 0.9rem;'>Delete</a>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No complaints found matching your criteria.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('live_search');
    const tableBody = document.querySelector('.table tbody');

    searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value;

        // AJAX Request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax_search_complaints.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (this.status === 200) {
                tableBody.innerHTML = this.responseText;
            }
        }

        xhr.send('search=' + searchTerm);
    });
</script>

<?php include '../includes/footer.php'; ?>