<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Prevent deleting self
    if ($delete_id == $_SESSION['user_id']) {
        header("Location: users.php?msg=error");
        exit();
    }
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: users.php?msg=deleted");
    exit();
}
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Manage Users</h2>
                    <a href="add_user.php" class="btn btn-primary">Add New User</a>
                </div>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success">User deleted successfully.</div>
            <?php endif; ?>
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
                <div class="alert alert-danger">Cannot delete yourself.</div>
            <?php endif; ?>

            <!-- Search Form -->
            <div style="margin-bottom: 20px;">
                <input type="text" id="live_search_users" class="form-control"
                    placeholder="Live Search Users by Name, Email...">
            </div>

            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="users_table_body">
                        <?php
                        $sql = "SELECT * FROM users ORDER BY id ASC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . $row['role'] . "</td>";
                                echo "<td>" . $row['status'] . "</td>";
                                echo "<td>
                                        <a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-primary' style='padding: 5px 10px; font-size: 0.9rem;'>Edit</a>
                                        <a href='users.php?delete_id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")' style='padding: 5px 10px; font-size: 0.9rem;'>Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('live_search_users');
    const tableBody = document.getElementById('users_table_body');

    searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value;

        // AJAX Request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax_search_users.php', true);
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