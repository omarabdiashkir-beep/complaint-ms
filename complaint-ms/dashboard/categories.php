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
    $description = trim($_POST['description']);

    if (!empty($name)) {
        // Check duplicate
        $check = $conn->query("SELECT id FROM categories WHERE name='$name'");
        if ($check->num_rows > 0) {
            $error = "Category already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $description);
            if ($stmt->execute()) {
                $msg = "Category added successfully.";
            } else {
                $error = "Error adding category.";
            }
        }
    } else {
        $error = "Category Name is required.";
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM categories WHERE id = $delete_id");
    header("Location: categories.php?msg=deleted");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted')
    $msg = "Category deleted successfully.";
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <h2>Manage Categories</h2>
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
                <h3>Add New Category</h3>
                <form action="categories.php" method="POST">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>

            <!-- List -->
            <div class="card">
                <h3>Existing Categories</h3>
                <div style="margin-bottom: 20px;">
                    <input type="text" id="live_search_categories" class="form-control"
                        placeholder="Search Categories...">
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="categories_table_body">
                        <?php
                        $result = $conn->query("SELECT * FROM categories ORDER BY id ASC");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                                echo "<td>
                                        <a href='categories.php?delete_id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")' style='padding: 5px 10px; font-size: 0.9rem;'>Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No categories found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('live_search_categories');
    const tableBody = document.getElementById('categories_table_body');

    searchInput.addEventListener('keyup', function () {
        const searchTerm = this.value;

        // AJAX Request
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax_search_categories.php', true);
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