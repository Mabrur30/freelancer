<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$logged_in_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch other user's name
$other_user = $conn->query("SELECT name FROM users WHERE id = $other_user_id")->fetch_assoc();

// Handle message send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
  $message = trim($_POST['message']);
  if ($message !== '') {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $logged_in_id, $other_user_id, $message);
    $stmt->execute();
    header("Location: view_messages.php?user_id=" . $other_user_id);
    exit;
  }
}

// Fetch all messages between both users
$sql = "
  SELECT m.*, u.name AS sender_name 
  FROM messages m 
  JOIN users u ON m.sender_id = u.id 
  WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
  ORDER BY m.sent_at ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $logged_in_id, $other_user_id, $other_user_id, $logged_in_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Messages with <?= htmlspecialchars($other_user['name']) ?></title>
  <link rel="stylesheet" href="message_style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h3>💬 Chat with <?= htmlspecialchars($other_user['name']) ?></h3>
  <div class="chat-box border rounded p-3 mb-3" style="height: 400px; overflow-y: scroll;">
    <?php while ($msg = $messages->fetch_assoc()): ?>
      <div class="mb-2 <?= $msg['sender_id'] == $logged_in_id ? 'text-end' : 'text-start' ?>">
        <div class="message-bubble <?= $msg['sender_id'] == $logged_in_id ? 'sent' : 'received' ?>">
          <small><strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong></small><br>
          <?= nl2br(htmlspecialchars($msg['message'])) ?><br>
          <small class="text-muted"><?= date('M j, g:i a', strtotime($msg['sent_at'])) ?></small>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <form method="POST" class="d-flex">
    <input type="text" name="message" class="form-control me-2" placeholder="Type a message..." required>
    <button type="submit" class="btn btn-primary">Send</button>
  </form>

  <a href="inbox.php" class="btn btn-secondary mt-3">Back to Inbox</a>
</div>
</body>
</html>
