<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$msg = '';
$error = '';

// Handle Add
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);

    if (!empty($name)) {
        // Check duplicate
        $check = $conn->query("SELECT id FROM departments WHERE name='$name'");
        if ($check->num_rows > 0) {
            $error = "Department already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $code);
            if ($stmt->execute()) {
                $msg = "Department added successfully.";
            } else {
                $error = "Error adding department.";
            }
        }
    } else {
        $error = "Department Name is required.";
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM departments WHERE id = $delete_id");
    header("Location: departments.php?msg=deleted");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted')
    $msg = "Department deleted successfully.";
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <h2>Manage Departments</h2>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Add Form -->
            <div class="card">
                <h3>Add New Department</h3>
                <form action="departments.php" method="POST">
                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Department Code (e.g., CS, ENG)</label>
                        <input type="text" name="code" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Department</button>
                </form>
            </div>

            <!-- List -->
            <div class="card">
                <h3>Existing Departments</h3>
                <div style="margin-bottom: 20px;">
                    <input type="text" id="live_search_departments" class="form-control"
                        placeholder="Search Departments...">
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="departments_table_body">
                        <?php
                        $result = $conn->query("SELECT * FROM departments ORDER BY id ASC");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['code']) . "</td>";
                                echo "<td>
                                        <a href='departments.php?delete_id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")' style='padding: 5px 10px; font-size: 0.9rem;'>Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No departments found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('live_search_departments');
    const tableBody = document.getElementById('departments_table_body');

    searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value;

        // AJAX Request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax_search_departments.php', true);
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