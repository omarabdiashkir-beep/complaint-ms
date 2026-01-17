<?php
include 'includes/db_connect.php';
include 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="content">
        <!-- Navbar -->
        <?php include 'includes/navbar.php'; ?>

        <div class="main-content">
            <div class="card">
                <h1>Welcome to Jamhuriya University Complaint Management System</h1>
                <p>We value your feedback. This platform allows students to submit complaints and track their resolution
                    status.</p>
            </div>

            <div class="card">
                <div class="card-header">
                    Our Departments
                </div>
                <p>Loading content from database...</p>
                <ul style="list-style: square; padding-left: 20px; margin-top: 15px;">
                    <?php
                    $sql = "SELECT name, code FROM departments";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<li><strong>" . htmlspecialchars($row["name"]) . "</strong>: " . htmlspecialchars($row["code"]) . "</li>";
                        }
                    } else {
                        echo "<li>No departments found.</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>