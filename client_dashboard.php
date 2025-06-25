<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
  header("Location: login.php");
  exit;
}

$client_id = $_SESSION['user_id'];

// Fetch jobs posted by this client
$jobs = [];
$jobs_sql = "SELECT * FROM jobs WHERE client_id = $client_id ORDER BY created_at DESC";
$jobs_result = $conn->query($jobs_sql);
if ($jobs_result && $jobs_result->num_rows > 0) {
  while ($row = $jobs_result->fetch_assoc()) {
    $jobs[] = $row;
  }
}

// Fetch applications received for client jobs

$applications_sql = "
  SELECT a.*, u.name AS freelancer_name, u.id AS freelancer_id, j.title AS job_title
  FROM applications a
  JOIN users u ON a.freelancer_id = u.id
  JOIN jobs j ON a.job_id = j.id
  WHERE j.client_id = $client_id AND a.status != 'accepted'
  ORDER BY a.submitted_at DESC
";

$applications_result = $conn->query($applications_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Client Dashboard | WorkNest</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="client_dashboard.css">
</head>
<body>
<div class="dashboard container">

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="text-center text-success flex-grow-1">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h1>
       <a href="inbox.php" class="btn btn-outline-secondary mb-3">
  <i class="fas fa-envelope"></i> Inbox
</a>
  <a href="logout.php" class="btn btn-outline ms-3">Logout</a>
</div>

  <!-- SECTION 1: My Posted Jobs -->
  <section class="section-box">
    <h2><i class="fas fa-briefcase"></i> My Posted Jobs</h2>
    <?php if ($jobs): ?>
      <ul class="list-unstyled">
        <?php foreach ($jobs as $job): ?>
          <li class="job-item">
  <strong><?= htmlspecialchars($job['title']) ?></strong><br>
  Budget: ৳<?= $job['budget'] ?> <br>
  Deadline: <?= $job['deadline'] ?><br>
  Posted: <?= date('F j, Y', strtotime($job['created_at'])) ?><br>
  <a href="edit_job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
  <form action="delete_job.php" method="post" style="display:inline-block" onsubmit="return confirm('Delete this job?');">
    <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
  </form>
</li>

        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-muted">You haven’t posted any jobs yet.</p>
    <?php endif; ?>
  </section>

  <!-- SECTION 2: Applications Received -->
  <section class="section-box">
  <h2><i class="fas fa-inbox"></i> Applications Received</h2>
  <?php if ($applications_result && $applications_result->num_rows > 0): ?>
    <?php while ($app = $applications_result->fetch_assoc()): ?>
      <div class="application-box border rounded p-3 mb-4 shadow-sm">
        <h5><?= htmlspecialchars($app['job_title']) ?></h5>
        <p><strong>Freelancer:</strong> <?= htmlspecialchars($app['freelancer_name']) ?></p>
        <p><strong>Proposal:</strong><br><?= nl2br(htmlspecialchars($app['proposal'])) ?></p>
        <p><strong>Submitted:</strong> <?= date('F j, Y', strtotime($app['submitted_at'])) ?></p>

        <div class="d-flex gap-2 mt-2">
          <form method="POST" action="manage_application.php">
            <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
            <button name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
            <button name="action" value="reject" class="btn btn-outline-danger btn-sm">Reject</button>
          </form>
          <a href="view_messages.php?user_id=<?= $app['freelancer_id'] ?>" class="btn btn-info btn-sm">
            Message
          </a>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">No applications yet.</p>
  <?php endif; ?>
</section>

<!-- SECTION: Ongoing Work -->

<section class="section-box">
  <h2><i class="fas fa-briefcase"></i> Ongoing Work</h2>
  <?php
    $ongoing_sql = "
  SELECT a.*, u.name AS freelancer_name, j.title AS job_title
  FROM applications a
  JOIN users u ON a.freelancer_id = u.id
  JOIN jobs j ON a.job_id = j.id
  WHERE j.client_id = $client_id AND a.status = 'accepted' AND a.is_completed = 0
  ORDER BY a.submitted_at DESC
";

    $ongoing_result = $conn->query($ongoing_sql);
  ?>

  <?php if ($ongoing_result && $ongoing_result->num_rows > 0): ?>
    <?php while ($ongoing = $ongoing_result->fetch_assoc()): ?>
      <div class="application-box border rounded p-3 mb-4 shadow-sm">
        <h5><?= htmlspecialchars($ongoing['job_title']) ?></h5>
        <p><strong>Freelancer:</strong> <?= htmlspecialchars($ongoing['freelancer_name']) ?></p>
        <p><strong>Proposal:</strong><br><?= nl2br(htmlspecialchars($ongoing['proposal'])) ?></p>
        <p><strong>Accepted On:</strong> <?= date('F j, Y', strtotime($ongoing['submitted_at'])) ?></p>
        <div class="d-flex gap-2">
          <a href="view_messages.php?user_id=<?= $ongoing['freelancer_id'] ?>" class="btn btn-info btn-sm">Message</a>
          <form method="POST" action="mark_completed.php" onsubmit="return confirm('Mark this job as completed? This will remove it permanently.');">
            <input type="hidden" name="app_id" value="<?= $ongoing['id'] ?>">
            <button type="submit" class="btn btn-success btn-sm">Mark as Completed</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">No ongoing work yet.</p>
  <?php endif; ?>
</section>



  <!-- SECTION 3: Post a New Job -->
  <section class="section-box">
    <h2><i class="fas fa-plus-circle"></i> Post a New Job</h2>
    <form method="post" action="post_job.php">
      <div class="mb-3">
        <input type="text" name="title" class="form-control" placeholder="Job Title" required>
      </div>
      <div class="mb-3">
        <textarea name="description" class="form-control" placeholder="Job Description" required></textarea>
      </div>
      <div class="row mb-3">
        <div class="col-md-4">
          <input type="number" name="budget" class="form-control" placeholder="Budget in ৳" required>
        </div>
        <div class="col-md-4">
          <input type="date" name="deadline" class="form-control" required>
        </div>
        <div class="col-md-4">
          <input type="text" name="category" class="form-control" placeholder="Category (e.g. Design)" required>
        </div>
      </div>
      <button type="submit" class="btn btn-custom">Post Job</button>
    </form>
    
  </section>
  

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 