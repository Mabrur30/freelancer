<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $client_id = $_SESSION['user_id'];
    $job_id = intval($_POST['job_id']);

    // Delete only if job belongs to this client
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $job_id, $client_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: client_dashboard.php?msg=job_deleted");
        exit;
    } else {
        $error = "Failed to delete job.";
    }
} else {
    header("Location: client_dashboard.php");
    exit;
}
?>
