<?php
include '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    exit('Unauthorized');
}

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$sql = "SELECT * FROM users";

if (!empty($search)) {
    $escaped_search = $conn->real_escape_string($search);
    $term = "%$escaped_search%";
    $sql .= " WHERE (username LIKE '$term' OR first_name LIKE '$term' OR last_name LIKE '$term' OR email LIKE '$term' OR id LIKE '$term')";
}

$sql .= " ORDER BY id ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
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
    echo "<tr><td colspan='7' style='text-align:center;'>No users found.</td></tr>";
}
?>