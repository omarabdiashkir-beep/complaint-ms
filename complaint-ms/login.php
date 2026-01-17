<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $sql = "SELECT id, username, password, role, first_name, last_name, status FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] == 'inactive') {
                $error = "Your account is inactive. Please contact admin.";
            } elseif (password_verify($password, $user['password'])) {
                // Password valid
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['last_activity'] = time();

                // Handle Remember Me
                if ($remember) {
                    setcookie("username", $username, time() + (86400 * 30), "/"); // 30 days
                    setcookie("password", $password, time() + (86400 * 30), "/"); // INSECURE practice for demo only, usually token based
                } else {
                    if (isset($_COOKIE["username"])) {
                        setcookie("username", "", time() - 3600, "/");
                    }
                    if (isset($_COOKIE["password"])) {
                        setcookie("password", "", time() - 3600, "/");
                    }
                }

                header("Location: dashboard/dashboard.php");
                exit();
            } else {
                $error = "Invalid password. Please check your spelling.";
            }
        } else {
            $error = "User not found. Please register or contact admin.";
        }
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Login</h2>
            <p>Access your dashboard</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php
        if (isset($_GET['timeout'])) {
            echo '<div class="alert alert-danger">Your session has expired. Please login again.</div>';
        }
        ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php if (isset($_COOKIE["username"])) {
                    echo $_COOKIE["username"];
                } ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember" <?php if (isset($_COOKIE["username"])) {
                        echo "checked";
                    } ?>>
                    Remember Me
                </label>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>