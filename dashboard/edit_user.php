<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];
$message = '';
$error = '';

$sql = "SELECT * FROM users WHERE id = $id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, role=?, status=?, password=? WHERE id=?");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $role, $status, $hashed, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, role=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $role, $status, $id);
    }

    if ($stmt->execute()) {
        $message = "User updated successfully.";
        // Refresh
        $user['first_name'] = $first_name;
        $user['last_name'] = $last_name;
        $user['email'] = $email;
        $user['role'] = $role;
        $user['status'] = $status;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<div class="wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <?php include '../includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <h2>Edit User</h2>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="edit_user.php?id=<?php echo $id; ?>" method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control"
                            value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control"
                            value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control"
                            value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password (Leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="student" <?php if ($user['role'] == 'student')
                                echo 'selected'; ?>>Student
                            </option>
                            <option value="admin" <?php if ($user['role'] == 'admin')
                                echo 'selected'; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="active" <?php if ($user['status'] == 'active')
                                echo 'selected'; ?>>Active
                            </option>
                            <option value="inactive" <?php if ($user['status'] == 'inactive')
                                echo 'selected'; ?>
                                >Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="users.php" class="btn btn-danger">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>