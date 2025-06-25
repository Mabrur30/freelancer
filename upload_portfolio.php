<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'freelancer') {
  header("Location: login.php");
  exit;
}

$freelancer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $project_url = trim($_POST['project_url'] ?? '');

  // Image upload check
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed_ext)) {
      $new_name = uniqid('portfolio_') . "." . $ext;
      // Fixed path here — added slash after uploads/
      $upload_dir = "uploads/upload_portfolio/";
      $upload_path = $upload_dir . $new_name;

      // Make sure the directory exists
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }

      if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO portfolio (freelancer_id, title, description, image_url, project_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $freelancer_id, $title, $description, $upload_path, $project_url);
        if ($stmt->execute()) {
          header("Location: freelancer_dashboard.php?portfolio=success");
          exit;
        } else {
          echo "❌ Failed to insert into database.";
        }
        $stmt->close();
      } else {
        echo "❌ Failed to move uploaded file.";
      }
    } else {
      echo "❌ Invalid file format. Allowed: jpg, jpeg, png, gif.";
    }
  } else {
    echo "❌ Image upload failed or not provided.";
  }
}
?>

