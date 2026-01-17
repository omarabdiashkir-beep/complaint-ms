<?php
include '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

// Base Query
$sql = "SELECT c.*, cat.name as category_name, u.first_name, u.last_name 
        FROM complaints c 
        JOIN categories cat ON c.category_id = cat.id 
        JOIN users u ON c.user_id = u.id";

$where_clauses = [];

// Role Filter
if ($role != 'admin') {
    $where_clauses[] = "c.user_id = $user_id";
}

// Search Filter
if (!empty($search)) {
    $escaped_search = $conn->real_escape_string($search);
    $term = "%$escaped_search%";
    $where_clauses[] = "(c.id LIKE '$term' OR c.title LIKE '$term' OR c.status LIKE '$term' OR c.priority LIKE '$term' OR cat.name LIKE '$term' OR u.first_name LIKE '$term' OR u.last_name LIKE '$term')";
}

if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

$sql .= " ORDER BY c.created_at ASC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Status badge colors (Match style.css)
        $status_bg = '#eee';
        $status_text_color = '#333';

        if ($row['status'] == 'Pending') {
            $status_bg = '#ffc107';
            $status_text_color = '#000';
        }
        if ($row['status'] == 'In Progress') {
            $status_bg = '#fd7e14';
            $status_text_color = '#fff';
        }
        if ($row['status'] == 'Resolved') {
            $status_bg = '#28a745';
            $status_text_color = '#fff';
        }

        // Priority colors
        $priority_color = 'green';
        if ($row['priority'] == 'Medium')
            $priority_color = 'orange';
        if ($row['priority'] == 'High')
            $priority_color = 'red';

        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
        if ($role == 'admin') {
            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        }
        echo "<td><span style='color: $priority_color; font-weight:bold;'>" . $row['priority'] . "</span></td>";
        echo "<td><span class='badge' style='background-color: $status_bg; color: $status_text_color;'>" . $row['status'] . "</span></td>";
        echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
        echo "<td>
                <a href='view_complaint.php?id=" . $row['id'] . "' class='btn btn-primary' style='padding: 5px 10px; font-size: 0.9rem;'>View</a>";
        if ($role == 'admin') {
            echo " <a href='complaints.php?delete_id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")' style='padding: 5px 10px; font-size: 0.9rem;'>Delete</a>";
        }
        echo "</td>";
        echo "</tr>";
    }
} else {
    $colspan = ($role == 'admin') ? 8 : 7; // Increased colspan by 1 for the new Priority column
    echo "<tr><td colspan='$colspan' style='text-align:center;'>No results found.</td></tr>";
}
?>