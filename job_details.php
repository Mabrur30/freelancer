<?php
include 'config.php';

if (!isset($_GET['id'])) {
  echo "Invalid Job ID.";
  exit;
}

$job_id = intval($_GET['id']);
$sql = "SELECT * FROM jobs WHERE id = $job_id LIMIT 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
  echo "Job not found.";
  exit;
}

$job = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($job['title']) ?> | WorkNest</title>
  <link rel="stylesheet" href="job_details.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<header>
  <div class="container nav">
    <div class="logo">
      <i class="fas fa-feather-alt"></i>
      <span>WorkNest</span>
    </div>
    <nav>
      <a href="index.php">Home</a>
      <a href="browse_jobs.php">Browse Jobs</a>
      <a href="freelancer_dashboard.php">Freelancers</a>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    </nav>
  </div>
</header>

<section class="job-details">
  <div class="container">
    <h1><?= htmlspecialchars($job['title']) ?></h1>
    <p class="meta"><strong>Budget:</strong> ৳<?= htmlspecialchars($job['budget']) ?> | 
    <strong>Deadline:</strong> <?= htmlspecialchars($job['deadline']) ?> |
    <strong>Posted on:</strong> <?= date('F j, Y', strtotime($job['created_at'])) ?></p>

    <div class="job-description">
      <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
    </div>

    <a href="apply.php?job_id=<?= $job['id'] ?>" class="cta">Apply for this Job</a>
  </div>
</section>

<footer>
  <p>&copy; 2025 WorkNest. All rights reserved.</p>
</footer>

</body>
</html>
