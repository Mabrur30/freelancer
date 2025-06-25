<?php
session_start();
include 'config.php';

// ✅ Must be logged in and a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'freelancer') {
  header("Location: login.php");
  exit;
}

$freelancer_id = $_SESSION['user_id'];
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$job = null;
$error = '';
$success = '';

// ✅ Get job info
if ($job_id > 0) {
  $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
  $stmt->bind_param("i", $job_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $job = $result->fetch_assoc();
  } else {
    $error = "Job not found.";
  }
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $job) {
  $proposal = trim($_POST['proposal']);

  // Check for duplicate application
  $check = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND freelancer_id = ?");
  $check->bind_param("ii", $job_id, $freelancer_id);
  $check->execute();
  $check_result = $check->get_result();

  if ($check_result->num_rows > 0) {
    $error = "You already applied to this job.";
  } else {
    $insert = $conn->prepare("INSERT INTO applications (job_id, freelancer_id, proposal) VALUES (?, ?, ?)");
    $insert->bind_param("iis", $job_id, $freelancer_id, $proposal);

    if ($insert->execute()) {
      $success = "✅ Application submitted successfully!";
    } else {
      $error = "❌ Failed to submit application.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Apply to Job | WorkNest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <div class="card p-4 shadow">
    <h2 class="mb-3">Apply to: <?= htmlspecialchars($job['title'] ?? 'Unknown Job') ?></h2>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
      <a href="freelancer_dashboard.php" class="btn btn-sm btn-primary mt-2">Back to Dashboard</a>
    <?php else: ?>
      <p><strong>Budget:</strong> ৳<?= $job['budget'] ?> | <strong>Deadline:</strong> <?= $job['deadline'] ?></p>
      <p><?= nl2br(htmlspecialchars($job['description'] ?? '')) ?></p>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Your Proposal</label>
          <textarea name="proposal" class="form-control" rows="6" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Submit Application</button>
        <a href="freelancer_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
      </form>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
