<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
  header("Location: login.php");
  exit;
}

$client_id = $_SESSION['user_id'];
$app_id = intval($_GET['app_id'] ?? 0);

$application_sql = "
  SELECT a.*, j.title, j.id AS job_id, u.name AS freelancer_name, u.id AS freelancer_id
  FROM applications a
  JOIN jobs j ON a.job_id = j.id
  JOIN users u ON a.freelancer_id = u.id
  WHERE a.id = ? AND j.client_id = ?
";
$stmt = $conn->prepare($application_sql);
$stmt->bind_param("ii", $app_id, $client_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
  header("Location: client_dashboard.php");
  exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rating = intval($_POST['rating']);
  $comment = trim($_POST['comment']);

  if ($rating >= 1 && $rating <= 5 && $comment) {
    $review_stmt = $conn->prepare("INSERT INTO review (reviewer_id, reviewed_id, job_id, rating, comment, reviewed_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $review_stmt->bind_param("iiiis", $client_id, $application['freelancer_id'], $application['job_id'], $rating, $comment);
    $review_stmt->execute();

    // ✅ Delete application after review
    $update = $conn->prepare("UPDATE applications SET is_completed = 1 WHERE id = ?");
$update->bind_param("i", $app_id);
$update->execute();


    header("Location: client_dashboard.php?reviewed=1");
    exit;
  } else {
    $message = "Please give a valid rating and comment.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leave a Review | WorkNest</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <div class="card p-4">
    <h3>Leave a Review for <?= htmlspecialchars($application['freelancer_name']) ?> (Job: <?= htmlspecialchars($application['title']) ?>)</h3>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label for="rating">Rating (1 to 5)</label>
        <select name="rating" id="rating" class="form-select" required>
          <option value="">Select</option>
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?> ⭐</option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="comment">Review Message</label>
        <textarea name="comment" class="form-control" rows="4" required></textarea>
      </div>
      <button type="submit" class="btn btn-success">Submit Review</button>
      <a href="client_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</div>
</body>
</html>
