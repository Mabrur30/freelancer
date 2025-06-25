<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget = floatval($_POST['budget'] ?? 0);
    $deadline = $_POST['deadline'] ?? '';
    $category = trim($_POST['category'] ?? '');

    // Basic validation
    if ($title && $description && $budget > 0 && $deadline && $category) {
        // Prepare and execute insert query
        $stmt = $conn->prepare("INSERT INTO jobs (client_id, title, description, budget, deadline, category, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issdss", $client_id, $title, $description, $budget, $deadline, $category);

        if ($stmt->execute()) {
            // Success: redirect back to client dashboard
            header("Location: client_dashboard.php?msg=job_posted");
            exit;
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields correctly.";
    }
} else {
    // Redirect if accessed without POST
    header("Location: client_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Post Job | WorkNest</title>
    <link rel="stylesheet" href="client_dashboard.css" />
</head>
<body>
<div class="container">
    <h1>Post a New Job</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <a href="client_dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
