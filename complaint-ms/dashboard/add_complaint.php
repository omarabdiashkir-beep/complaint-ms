<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

if ($_SESSION['role'] != 'student') {
    header("Location: dashboard.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];
    $file_path = NULL;

    // Handle File Upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $filename = $_FILES['attachment']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $upload_dir = '../uploads/complaints/';
            if (!file_exists($upload_dir))
                mkdir($upload_dir, 0777, true);

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $new_name)) {
                $file_path = $new_name;
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "Invalid file type. Allowed: jpg, png, pdf, doc.";
        }
    }

    if (empty($error)) {
        if (!empty($title) && !empty($description) && !empty($category_id)) {
            $stmt = $conn->prepare("INSERT INTO complaints (user_id, category_id, title, description, priority, file_attachment) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $user_id, $category_id, $title, $description, $priority, $file_path);

            if ($stmt->execute()) {
                $message = "Complaint submitted successfully!";

                // Notify Admins
                $admin_sql = "SELECT id FROM users WHERE role = 'admin'";
                $admin_res = $conn->query($admin_sql);
                while ($admin = $admin_res->fetch_assoc()) {
                    $notif_msg = "New $priority priority complaint: " . htmlspecialchars($title);
                    $n_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $n_stmt->bind_param("is", $admin['id'], $notif_msg);
                    $n_stmt->execute();
                }
            } else {
                $error = "Error: " . $stmt->error;
            }
        } else {
            $error = "All fields are required.";
        }
    }
}
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <h2>Lodge a Complaint</h2>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <form action="add_complaint.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            $cats = $conn->query("SELECT * FROM categories");
                            while ($c = $cats->fetch_assoc()) {
                                echo "<option value='" . $c['id'] . "'>" . htmlspecialchars($c['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" class="form-control">
                            <option value="Low">Low (Green)</option>
                            <option value="Medium">Medium (Orange)</option>
                            <option value="High">High (Red)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Attachment (Image/PDF/Doc) - Optional</label>
                        <input type="file" name="attachment" class="form-control" style="padding: 10px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
                    <a href="complaints.php" class="btn btn-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>