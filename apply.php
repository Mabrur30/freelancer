<?php
session_start();
include 'config.php';

// Must be logged in and freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['uuser_role'] !== 'freelancer') {
  header("Location: login.php");
  exit;
}

$freelancer_id = $_SESSION['user_id'];
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// Get job info for title
$job = null;
if ($job_id) {
  $res = $conn->query("SELECT title FROM jobs WHERE id = $job_id");
  if ($res && $res->num_rows > 0) {
    $job = $res->fetch_assoc();
  }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $proposal = $conn->real_escape_string($_POST['proposal']);

  $check = $conn->query("SELECT * FROM applications WHERE job_id = $job_id AND freelancer_id = $freelancer_id");
  if ($check && $check->num_rows > 0) {
    $error = "You already applied to this job.";
  } else {
    $sql = "INSERT INTO applications (job_id, freelancer_id, proposal) VALUES ($job_id, $freelancer_id, '$proposal')";
    if ($conn->query($sql)) {
      $success = "Application submitted successfully!";
    } else {
      $error = "Error submitting application.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Apply for Job | WorkNest</title>
  <link rel="stylesheet" href="apply.css">
</head>
<body>

<div class="apply-container">
  <h1>Apply to: <?= htmlspecialchars($job['title'] ?? 'Unknown Job') ?></h1>

  <?php if (isset($error)): ?>
    <p class="error"><?= $error ?></p>
  <?php elseif (isset($success)): ?>
    <p class="success"><?= $success ?></p>
  <?php endif; ?>

  <form method="POST">
    <label for="proposal">Your Proposal:</label><br>
    <textarea name="proposal" id="proposal" rows="6" required></textarea><br>
    <button type="submit">Submit Application</button>
  </form>
</div>

</body>
</html>
