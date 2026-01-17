<?php
include '../includes/session_check.php';
include '../includes/session_expire.php';
include '../includes/db_connect.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    header("Location: notifications.php");
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
                    <h2>Notifications</h2>
                    <a href="notifications.php?mark_read=true" class="btn btn-primary">Mark All as Read</a>
                </div>
            </div>

            <div class="card">
                <ul style="list-style: none; padding: 0;">
                    <?php
                    $sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $bg = $row['is_read'] ? '#fff' : '#f0f4f8'; // Highlight unread
                            $border = $row['is_read'] ? '#eee' : '#3498db';

                            echo "<li style='background: $bg; border-left: 4px solid $border; padding: 15px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);'>";
                            echo "<div style='display: flex; justify-content: space-between;'>";
                            echo "<span>" . htmlspecialchars($row['message']) . "</span>";
                            echo "<small style='color: #888;'>" . date('M d, H:i', strtotime($row['created_at'])) . "</small>";
                            echo "</div>";
                            echo "</li>";
                        }
                    } else {
                        echo "<li>No notifications found.</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>