<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: complaints.php");
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch complaint details with user info
$sql = "SELECT c.*, cat.name as category_name, u.first_name, u.last_name, u.email, u.phone 
        FROM complaints c 
        JOIN categories cat ON c.category_id = cat.id 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Complaint not found.";
    exit();
}

$row = $result->fetch_assoc();

// Security: If student, ensure they own the complaint
if ($role == 'student' && $row['user_id'] != $user_id) {
    echo "Unauthorized access.";
    exit();
}

// Handle Status Update (Admin Only)
$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && $role == 'admin') {
    $new_status = $_POST['status'];
    $admin_file_path = NULL;

    // Handle Admin File Upload
    if (isset($_FILES['admin_file']) && $_FILES['admin_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $filename = $_FILES['admin_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = 'sol_' . uniqid() . '.' . $ext;
            $upload_dir = '../uploads/solutions/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            
            if (move_uploaded_file($_FILES['admin_file']['tmp_name'], $upload_dir . $new_name)) {
                $admin_file_path = $new_name;
            }
        }
    }

    $update_sql = "UPDATE complaints SET status = ?, updated_at = NOW()";
    $params = [$new_status];
    $types = "s";

    if ($admin_file_path) {
        $update_sql .= ", admin_attachment = ?";
        $params[] = $admin_file_path;
        $types .= "s";
    }

    $update_sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param($types, ...$params);

    if ($update_stmt->execute()) {
        $msg = "Status updated successfully.";
        // Refresh data
        $row['status'] = $new_status;
        if($admin_file_path) $row['admin_attachment'] = $admin_file_path;
        $row['updated_at'] = date('Y-m-d H:i:s');

        // Notify Student
        $student_id = $row['user_id'];
        $notif_msg = "Your complaint #$id status has been updated to: $new_status";
        if($admin_file_path) $notif_msg .= ". An attachment has been added.";
        
        $n_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $n_stmt->bind_param("is", $student_id, $notif_msg);
        $n_stmt->execute();
    } else {
        $msg = "Error updating status.";
    }
}
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between;">
                    <h2>Complaint Details #
                        <?php echo $row['id']; ?>
                    </h2>
                    <a href="complaints.php" class="btn btn-secondary"
                        style="background: #95a5a6; color:white;">Back</a>
                </div>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div style="display: flex; gap: 30px; flex-wrap: wrap;">
                    <div style="flex: 2;">
                        <h3>
                            <?php echo htmlspecialchars($row['title']); ?>
                        </h3>
                        <p style="color: #7f8c8d; margin-bottom: 20px;">Category:
                            <?php echo htmlspecialchars($row['category_name']); ?>
                        </p>

                        <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; border: 1px solid #eee;">
                            <p>
                                <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                            </p>
                        </div>

                        <div style="margin-top: 20px; font-size: 0.9rem; color: #7f8c8d;">
                            Created at:
                            <?php echo $row['created_at']; ?><br>
                            Last Updated:
                            <?php echo $row['updated_at']; ?>
                        </div>
                    </div>

                    <div style="flex: 1; border-left: 1px solid #eee; padding-left: 20px;">
                        <h4>Current Status</h4>
                        <?php
                        $status_color = 'black';
                        if ($row['status'] == 'Pending')
                            $status_color = '#f1c40f';
                        if ($row['status'] == 'In Progress')
                            $status_color = '#e67e22';
                        if ($row['status'] == 'Resolved')
                            $status_color = '#2ecc71';

                        $priority_color = 'green';
                        if ($row['priority'] == 'Medium')
                            $priority_color = 'orange';
                        if ($row['priority'] == 'High')
                            $priority_color = 'red';
                        ?>

                        <p><strong>Status:</strong> <span
                                style="color: <?php echo $status_color; ?>; font-weight:bold;"><?php echo $row['status']; ?></span>
                        </p>
                        <p><strong>Priority:</strong> <span
                                style="color: <?php echo $priority_color; ?>; font-weight:bold;"><?php echo $row['priority']; ?></span>
                        </p>

                        <?php if ($row['file_attachment']): ?>
                            <p style="margin-top: 15px;">
                                <strong>Attachment:</strong><br>
                                <a href="../uploads/complaints/<?php echo $row['file_attachment']; ?>" target="_blank"
                                    class="btn btn-secondary"
                                    style="padding: 5px 10px; font-size: 0.8rem; margin-top:5px;">View File</a>
                            </p>
                        <?php endif; ?>

                        <?php if ($row['admin_attachment']): ?>
                            <p style="margin-top: 15px;">
                                <strong>Admin Solution:</strong><br>
                                <a href="../uploads/solutions/<?php echo $row['admin_attachment']; ?>" target="_blank"
                                    class="btn btn-success"
                                    style="padding: 5px 10px; font-size: 0.8rem; margin-top:5px;">View Solution</a>
                            </p>
                        <?php endif; ?>

                        <hr style="margin: 20px 0;">

                        <?php if ($role == 'admin'): ?>
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Update Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Pending" <?php if ($row['status'] == 'Pending')
                                            echo 'selected'; ?>>
                                            Pending</option>
                                        <option value="In Progress" <?php if ($row['status'] == 'In Progress')
                                            echo 'selected'; ?>>In Progress</option>
                                        <option value="Resolved" <?php if ($row['status'] == 'Resolved')
                                            echo 'selected'; ?>>
                                            Resolved</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Attach Solution (Optional)</label>
                                    <input type="file" name="admin_file" class="form-control" style="padding: 10px;">
                                </div>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </form>
                            <hr style="margin: 20px 0;">
                            <h4>User Details</h4>
                            <p><strong>Name:</strong>
                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>