<?php
include 'config.php'; // DB connection

$jobs = [];
$sql = "SELECT * FROM jobs ORDER BY created_at DESC LIMIT 6";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
  }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Browse Jobs | WorkNest</title>
  <link rel="stylesheet" href="browse_job.css" />
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

<section class="hero browse-hero">
  <div class="container hero-content">
    <h1>Find Freelance Jobs That Match Your Skills</h1>
    <p>Explore categories and apply to your next opportunity on WorkNest.</p>
    <a href="login.php" class="cta">Start Applying</a>
  </div>
</section>

<section class="categories">
  <div class="container">
    <h2>Popular Job Categories</h2>
    <div class="category-list">
      <div class="category"><i class="fas fa-code"></i> Development</div>
      <div class="category"><i class="fas fa-paint-brush"></i> Design</div>
      <div class="category"><i class="fas fa-pen-nib"></i> Writing</div>
      <div class="category"><i class="fas fa-chart-line"></i> Marketing</div>
    </div>
  </div>
</section>

<section class="latest-jobs">
  <div class="container">
    <h2>Latest Jobs</h2>
   <div class="jobs-grid">
  <?php foreach ($jobs as $job): ?>
    <div class="job-card">
      <h3><?= htmlspecialchars($job['title']) ?></h3>
      <p><?= htmlspecialchars($job['description']) ?></p>
      <p><strong>Budget:</strong> ৳<?= htmlspecialchars($job['budget']) ?></p>
      <p><strong>Deadline:</strong> <?= htmlspecialchars($job['deadline']) ?></p>
      <a href="job_details.php?id=<?= $job['id'] ?>" class="job-btn">View Details</a>
    </div>
  <?php endforeach; ?>
</div>

  </div>
</section>

<section class="why-worknest">
  <div class="container">
    <h2>Why Choose WorkNest?</h2>
    <div class="features">
      <div class="feature">
        <i class="fas fa-check-circle"></i>
        <h3>Verified Jobs</h3>
        <p>Every job is reviewed for quality and authenticity.</p>
      </div>
      <div class="feature">
        <i class="fas fa-lock"></i>
        <h3>Secure Payments</h3>
        <p>Clients fund projects before work begins.</p>
      </div>
      <div class="feature">
        <i class="fas fa-users"></i>
        <h3>Top Clients</h3>
        <p>Work with startups, agencies, and global companies.</p>
      </div>
    </div>
  </div>
</section>

<footer>
  <p>&copy; 2025 WorkNest. All rights reserved.</p>
</footer>

</body>
</html>
