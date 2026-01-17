<?php
// Determine current page for active highlighting
$current_page = basename($_SERVER['PHP_SELF']);
function isActive($page)
{
    global $current_page;
    return $current_page == $page ? 'active' : '';
}
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <h3>JUST</h3>
    </div>
    <ul class="components">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li>
                <a href="<?php echo $base_url; ?>dashboard/dashboard.php"
                    class="<?php echo isActive('dashboard.php'); ?>">Dashboard</a>
            </li>

            <?php
            // Notification Count
            $notif_sql = "SELECT count(*) as count FROM notifications WHERE user_id = " . $_SESSION['user_id'] . " AND is_read = 0";
            // Check if table exists first to avoid error before setup
            $notif_check = $conn->query("SHOW TABLES LIKE 'notifications'");
            $unread_count = 0;
            if ($notif_check->num_rows > 0) {
                $notif_res = $conn->query($notif_sql);
                if ($notif_res) {
                    $n_row = $notif_res->fetch_assoc();
                    $unread_count = $n_row['count'];
                }
            }
            ?>
            <li>
                <a href="<?php echo $base_url; ?>dashboard/notifications.php"
                    class="<?php echo isActive('notifications.php'); ?>">
                    Notifications
                    <?php if ($unread_count > 0): ?>
                        <span class="badge" style="background: #FF4C4C; margin-left: auto;"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <?php if ($role === 'student'): ?>
                <li>
                    <a href="<?php echo $base_url; ?>dashboard/add_complaint.php"
                        class="<?php echo isActive('add_complaint.php'); ?>">Lodge Complaint</a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>dashboard/complaints.php"
                        class="<?php echo isActive('complaints.php'); ?>">My Complaints</a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <li>
                    <a href="<?php echo $base_url; ?>dashboard/complaints.php"
                        class="<?php echo isActive('complaints.php'); ?>">Manage Complaints</a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>dashboard/users.php" class="<?php echo isActive('users.php'); ?>">Manage
                        Users</a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>dashboard/categories.php"
                        class="<?php echo isActive('categories.php'); ?>">Categories</a>
                </li>
                <li>
                    <a href="<?php echo $base_url; ?>dashboard/departments.php"
                        class="<?php echo isActive('departments.php'); ?>">Departments</a>
                </li>
            <?php endif; ?>

            <li>
                <a href="<?php echo $base_url; ?>dashboard/profile.php" class="<?php echo isActive('profile.php'); ?>">My
                    Profile</a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>logout.php">Logout</a>
            </li>

        <?php else: ?>
            <!-- Public Sidebar Links -->
            <li>
                <a href="<?php echo $base_url; ?>index.php" class="<?php echo isActive('index.php'); ?>">Home</a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>login.php" class="<?php echo isActive('login.php'); ?>">Login</a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>register.php" class="<?php echo isActive('register.php'); ?>">Register</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>