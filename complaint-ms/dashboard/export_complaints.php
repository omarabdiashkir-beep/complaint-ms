<?php
include '../includes/session_check.php';
include '../includes/db_connect.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=complaints_report.csv');

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, array('ID', 'Title', 'Category', 'Priority', 'Status', 'Date', 'Student Name'));

// Query
$sql = "SELECT c.id, c.title, c.priority, c.status, c.created_at, cat.name as category, u.first_name, u.last_name 
        FROM complaints c 
        JOIN categories cat ON c.category_id = cat.id 
        JOIN users u ON c.user_id = u.id";

if ($role != 'admin') {
    $sql .= " WHERE c.user_id = $user_id";
}

$sql .= " ORDER BY c.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            $row['id'],
            $row['title'],
            $row['category'],
            $row['priority'],
            $row['status'],
            $row['created_at'],
            $row['first_name'] . ' ' . $row['last_name']
        ));
    }
}

fclose($output);
exit();
?>