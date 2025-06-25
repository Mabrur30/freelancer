<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = intval($_POST['app_id']);
    $action = $_POST['action'];

    // Validate action
    // if (!in_array($action, ['accept', 'reject'])) {
    //     die("Invalid action.");
    // }
 if (in_array($action, ['accept', 'reject'])) {
    $status = $action === 'accept' ? 'Accepted' : 'Rejected';
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $app_id);
    $stmt->execute();
  }
  header("Location: client_dashboard.php?status=updated");
    // Check if this application belongs to a job posted by this client
    $stmt = $conn->prepare("
        SELECT a.id FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.id = ? AND j.client_id = ?
    ");
    $stmt->bind_param("ii", $app_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Unauthorized action.");
    }

    // Update the application status
    $new_status = ($action === 'accept') ? 'Accepted' : 'Rejected';
    $update_stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $app_id);

    if ($update_stmt->execute()) {
        $_SESSION['flash_message'] = "Application has been $new_status.";
    } else {
        $_SESSION['flash_message'] = "Failed to update application status.";
    }
} else {
    die("Invalid request.");
}

header("Location: client_dashboard.php");
exit;
