<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'freelancer') {
    header("Location: login.php");
    exit;
}

$freelancer_id = $_SESSION['user_id'];

// Fetch applied jobs

$applied_sql = "
  SELECT j.id AS job_id, j.title, j.client_id, a.id AS application_id, a.proposal, a.status, a.submitted_at
  FROM applications a
  JOIN jobs j ON a.job_id = j.id
  WHERE a.freelancer_id = $freelancer_id
  ORDER BY a.submitted_at DESC
";
$ongoing_sql = "
  SELECT a.*, j.title AS job_title, j.client_id, u.name AS client_name
  FROM applications a
  JOIN jobs j ON a.job_id = j.id
  JOIN users u ON j.client_id = u.id
  WHERE a.freelancer_id = $freelancer_id AND a.status = 'accepted'
  ORDER BY a.submitted_at DESC
";
$ongoing_result = $conn->query($ongoing_sql);

$applied_result = $conn->query($applied_sql);

// Fetch user info for profile section
$user_sql = "SELECT * FROM users WHERE id = $freelancer_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Freelancer Dashboard | WorkNest</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="freelancer_dashboard.css">
</head>
<body>
<div class="container mt-5">
  <div class="dashboard p-4 shadow rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-success flex-grow-1">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h1>
           <a href="inbox.php" class="btn btn-outline-secondary mb-3">
  <i class="fas fa-envelope"></i> Inbox
</a>
      <a href="logout.php" class="btn btn-outline ms-3">Logout</a>
    </div>
<!-- Section 6: Available Jobs to Apply -->
<section class="section-box mt-5">
  <h2><i class="fas fa-briefcase"></i> Browse Jobs & Apply</h2>
  <?php
  // Fetch jobs the freelancer has not applied to
  $available_jobs_sql = 
 "SELECT * FROM jobs ORDER BY created_at DESC";
    
  ;
  $available_jobs_result = $conn->query($available_jobs_sql);

  if ($available_jobs_result && $available_jobs_result->num_rows > 0):
  ?>
    <div class="row">
      <?php while ($job = $available_jobs_result->fetch_assoc()): ?>
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($job['title']) ?></h5>
              <p class="card-text"><?= htmlspecialchars($job['description']) ?></p>
              <p><strong>Budget:</strong> ৳<?= $job['budget'] ?> | <strong>Deadline:</strong> <?= $job['deadline'] ?></p>
              <a href="apply_job.php?job_id=<?= $job['id'] ?>" class="btn btn-outline-primary">Apply</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="text-muted">No new jobs available to apply.</p>
  <?php endif; ?>
</section>

    <!-- Section 1: Applied Jobs -->
    <section class="section-box mb-5">
  <h2><i class="fas fa-file-alt"></i> Jobs You've Applied To</h2>
  <?php if ($applied_result && $applied_result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Job Title</th>
            <th>Proposal</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $applied_result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= nl2br(htmlspecialchars(substr($row['proposal'], 0, 100))) ?>...</td>
              <td><?= htmlspecialchars($row['status'] ?? 'Pending') ?></td>
              <td><?= date('F j, Y', strtotime($row['submitted_at'])) ?></td>
              <td>
                <a href="view_messages.php?user_id=<?= $row['client_id'] ?>" class="btn btn-sm btn-primary">
                  Message Client
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-muted">You haven't applied to any jobs yet.</p>
  <?php endif; ?>
</section>

<!-- Section: Ongoing Work -->
<!-- Section: Ongoing Work -->
<section class="section-box mt-5">
  <h2><i class="fas fa-briefcase"></i> Ongoing Work</h2>

  <?php
    // updated query  ➜ only accepted & not completed
    $ongoing_sql = "
      SELECT a.*, j.title AS job_title,
             j.client_id, u.name AS client_name
      FROM applications a
      JOIN jobs j ON a.job_id = j.id
      JOIN users u ON j.client_id = u.id
      WHERE a.freelancer_id = $freelancer_id
        AND a.status = 'accepted'
        AND a.is_completed = 0
      ORDER BY a.submitted_at DESC
    ";
    $ongoing_result = $conn->query($ongoing_sql);
  ?>

  <?php if ($ongoing_result && $ongoing_result->num_rows > 0): ?>
    <?php while ($ongoing = $ongoing_result->fetch_assoc()): ?>
      <div class="application-box border rounded p-3 mb-4 shadow-sm">
        <h5><?= htmlspecialchars($ongoing['job_title']) ?></h5>
        <p><strong>Client:</strong> <?= htmlspecialchars($ongoing['client_name']) ?></p>
        <p><strong>Proposal:</strong><br><?= nl2br(htmlspecialchars($ongoing['proposal'])) ?></p>
        <p><strong>Accepted On:</strong> <?= date('F j, Y', strtotime($ongoing['submitted_at'])) ?></p>

        <!-- action buttons -->
        <div class="d-flex gap-2">
          <a href="view_messages.php?user_id=<?= $ongoing['client_id'] ?>"
             class="btn btn-info btn-sm">
             Message Client
          </a>
          <span class="badge bg-secondary align-self-center">In Progress</span>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">You don’t have any ongoing work yet.</p>
  <?php endif; ?>
</section>

<!-- Section: Completed Work -->
<section class="section-box mt-5">
  <h2><i class="fas fa-check-circle"></i> Completed Work</h2>

  <?php
    $completed_sql = "
      SELECT a.*, j.title AS job_title,
             j.client_id, u.name AS client_name
      FROM applications a
      JOIN jobs j ON a.job_id = j.id
      JOIN users u ON j.client_id = u.id
      WHERE a.freelancer_id = $freelancer_id
        AND a.status = 'accepted'
        AND a.is_completed = 1
      ORDER BY a.submitted_at DESC
    ";
    $completed_result = $conn->query($completed_sql);
  ?>

  <?php if ($completed_result && $completed_result->num_rows > 0): ?>
    <?php while ($completed = $completed_result->fetch_assoc()): ?>
      <div class="application-box border rounded p-3 mb-4 shadow-sm">
        <h5><?= htmlspecialchars($completed['job_title']) ?></h5>
        <p><strong>Client:</strong> <?= htmlspecialchars($completed['client_name']) ?></p>
        <p><strong>Proposal:</strong><br><?= nl2br(htmlspecialchars($completed['proposal'])) ?></p>
        <p><strong>Completed On:</strong> <?= date('F j, Y', strtotime($completed['submitted_at'])) ?></p>

        <!-- Message Client button -->
        <a href="view_messages.php?user_id=<?= $completed['client_id'] ?>" class="btn btn-outline-secondary btn-sm mb-2">
          Message Client
        </a>

        <!-- Fetch review -->
        <?php
          $job_id = $completed['job_id'];
          $review_stmt = $conn->prepare("SELECT rating, comment FROM review WHERE job_id = ? AND reviewed_id = ?");
          $review_stmt->bind_param("ii", $job_id, $freelancer_id);
          $review_stmt->execute();
          $review_result = $review_stmt->get_result();
          if ($review_result && $review_result->num_rows > 0):
            $review = $review_result->fetch_assoc();
        ?>
          <div class="mt-2 p-2 border bg-light rounded">
            <strong>Client's Review:</strong><br>
            <span>Rating: <?= str_repeat('⭐', (int)$review['rating']) ?> (<?= $review['rating'] ?>/5)</span><br>
            <em><?= htmlspecialchars($review['comment']) ?></em>
          </div>
        <?php else: ?>
          <p class="text-muted"><em>No review submitted yet.</em></p>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-muted">No completed work yet.</p>
  <?php endif; ?>
</section>


    <!-- Section 2: Update Profile -->
<section class="section-box mb-5">
  <h2><i class="fas fa-user-edit"></i> Edit Your Profile</h2>
  <form method="POST" action="update_profile.php" enctype="multipart/form-data" novalidate>
    <div class="mb-3">
      <label>Name</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>
    <div class="mb-3">
      <label>Password <small>(Leave blank to keep current)</small></label>
      <input type="password" name="password" class="form-control" minlength="6" placeholder="New password if you want to change" />
    </div>
    <div class="mb-3">
      <label>Bio</label>
      <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
      <label>Profile Picture</label>
      <input type="file" name="profile_image" class="form-control" accept="image/*" onchange="previewImage(event)">
      <img id="profilePreview" src="<?= htmlspecialchars($user['profile_image']) ?: 'default_avatar.png' ?>" alt="Profile Preview" style="max-width:150px; max-height:150px; border-radius:50%; margin-top:10px; object-fit:cover; border: 2px solid #31572c;">
    </div>
    <button type="submit" class="btn btn-primary">Update Profile</button>
    <a href="freelancer_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
  </form>
</section>

<script>
function previewImage(event) {
  const preview = document.getElementById('profilePreview');
  const file = event.target.files[0];
  if (file) {
    preview.src = URL.createObjectURL(file);
  }
}
</script>

    <!-- Section 3: View Public Profile -->
    <section class="section-box">
      <h2><i class="fas fa-id-badge"></i> Your Public Profile</h2>
      <p>You can preview your public profile as clients see it.</p>
      <a href="profile.php?id=<?= $freelancer_id ?>" class="btn btn-secondary">View Profile</a>
    </section>

    <!-- Section 4: Upload Portfolio -->
<section class="section-box mt-5">
  <h2><i class="fas fa-upload"></i> Upload Portfolio</h2>
  <form method="POST" action="upload_portfolio.php" enctype="multipart/form-data">
    <div class="mb-3">
      <label>Project Title</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control" rows="3" required></textarea>
    </div>
    <div class="mb-3">
      <label>Project Image</label>
      <input type="file" name="image" class="form-control" accept="image/*" required>
    </div>
    <div class="mb-3">
      <label>Project URL (optional)</label>
      <input type="url" name="project_url" class="form-control">
    </div>
    <button type="submit" class="btn btn-success">Upload Portfolio</button>
  </form>
</section>
<!-- Section 5: Your Portfolio -->
<section class="section-box mt-5">
  <h2><i class="fas fa-briefcase"></i> Your Portfolio</h2>
  <div class="row">
    <?php
    $portfolio_q = $conn->query("SELECT * FROM portfolio WHERE freelancer_id = $freelancer_id ORDER BY id DESC");
    if ($portfolio_q && $portfolio_q->num_rows > 0):
      while ($p = $portfolio_q->fetch_assoc()):
    ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="<?= htmlspecialchars($p['image_url']) ?>" class="card-img-top" alt="Project Image" style="height: 180px; object-fit: cover;">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($p['description']) ?></p>
            <?php if (!empty($p['project_url'])): ?>
              <a href="<?= htmlspecialchars($p['project_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Project</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; else: ?>
      <p class="text-muted">You haven't uploaded any portfolio items yet.</p>
    <?php endif; ?>
  </div>
</a>

</section>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
