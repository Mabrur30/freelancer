<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: client_dashboard.php");
    exit;
}

$job_id = intval($_GET['id']);

// Fetch job details and confirm ownership
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND client_id = ?");
$stmt->bind_param("ii", $job_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: client_dashboard.php");
    exit;
}

$job = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget = floatval($_POST['budget'] ?? 0);
    $deadline = $_POST['deadline'] ?? '';
    $category = trim($_POST['category'] ?? '');

    if ($title && $description && $budget > 0 && $deadline && $category) {
        $update_stmt = $conn->prepare("UPDATE jobs SET title=?, description=?, budget=?, deadline=?, category=? WHERE id=? AND client_id=?");
        $update_stmt->bind_param("ssdssii", $title, $description, $budget, $deadline, $category, $job_id, $client_id);
        if ($update_stmt->execute()) {
            $success = "Job updated successfully.";
            // Refresh job info
            $job['title'] = $title;
            $job['description'] = $description;
            $job['budget'] = $budget;
            $job['deadline'] = $deadline;
            $job['category'] = $category;
        } else {
            $error = "Update failed: " . $update_stmt->error;
        }
        $update_stmt->close();
    } else {
        $error = "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Job | WorkNest</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
  <h1>Edit Job</h1>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label>Title</label>
      <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($job['title']) ?>">
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control" required><?= htmlspecialchars($job['description']) ?></textarea>
    </div>
    <div class="mb-3">
      <label>Budget (৳)</label>
      <input type="number" name="budget" class="form-control" required value="<?= htmlspecialchars($job['budget']) ?>">
    </div>
    <div class="mb-3">
      <label>Deadline</label>
      <input type="date" name="deadline" class="form-control" required value="<?= htmlspecialchars($job['deadline']) ?>">
    </div>
    <div class="mb-3">
      <label>Category</label>
      <input type="text" name="category" class="form-control" required value="<?= htmlspecialchars($job['category']) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update Job</button>
    <a href="client_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
  </form>
</div>
</body>
</html>
