<?php
include '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    exit('Unauthorized');
}

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$sql = "SELECT * FROM departments";

if (!empty($search)) {
    $escaped_search = $conn->real_escape_string($search);
    $term = "%$escaped_search%";
    $sql .= " WHERE (name LIKE '$term' OR code LIKE '$term')";
}

$sql .= " ORDER BY id ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['code']) . "</td>";
        echo "<td>
                <a href='departments.php?delete_id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")' style='padding: 5px 10px; font-size: 0.9rem;'>Delete</a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4' style='text-align:center;'>No departments found.</td></tr>";
}
?>