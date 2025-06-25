<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'])) {
  $app_id = intval($_POST['app_id']);

  // Optional: verify that this application belongs to this client
  $client_id = $_SESSION['user_id'];
  $check = $conn->prepare("
    SELECT a.id FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ? AND j.client_id = ?
  ");
  $check->bind_param("ii", $app_id, $client_id);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    // ✅ Redirect to leave_review page with app_id
    header("Location: leave_review.php?app_id=$app_id");
    exit;
  }
}

header("Location: client_dashboard.php");
exit;
?>
