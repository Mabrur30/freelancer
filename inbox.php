<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// Fetch unique conversation partners
$sql = "
  SELECT u.id, u.name
  FROM users u
  WHERE u.id IN (
    SELECT DISTINCT IF(sender_id = ?, receiver_id, sender_id)
    FROM messages
    WHERE sender_id = ? OR receiver_id = ?
  )
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Inbox</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <h2>📥 Inbox</h2>

  <?php if ($conversations): ?>
    <ul class="list-group">
      <?php foreach ($conversations as $user): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <?= htmlspecialchars($user['name']) ?>
          <a href="view_messages.php?user_id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">Chat</a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="text-muted">No conversations yet.</p>
  <?php endif; ?>

  <a href="<?= $current_user_role === 'client' ? 'client_dashboard.php' : 'freelancer_dashboard.php' ?>" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
