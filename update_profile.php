<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'freelancer') {
  header("Location: login.php");
  exit;
}

$freelancer_id = $_SESSION['user_id'];
$message = '';
$success = '';

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $freelancer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $bio = trim($_POST['bio']);
  $password = $_POST['password'];
  $profile_image = $user['profile_image'];

  // Handle profile image upload
  if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    // Ensure upload dir exists
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }
    $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $file_name = 'profile_' . uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $file_path)) {
      $profile_image = $file_path;
    } else {
      $message = "Failed to upload profile image.";
    }
  }

  if (!$message) {
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $message = "Please enter a valid email address.";
    }
  }

  if (!$message) {
    // Check if email is used by another user
    $check_email_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("si", $email, $freelancer_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $message = "This email is already taken by another user.";
    }
    $stmt->close();
  }

  if (!$message) {
    $update_sql = "UPDATE users SET name=?, email=?, bio=?, profile_image=?";
    $params = [$name, $email, $bio, $profile_image];
    $types = "ssss";

    if (!empty($password)) {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $update_sql .= ", password=?";
      $params[] = $hashed_password;
      $types .= "s";
    }

    $update_sql .= " WHERE id=?";
    $params[] = $freelancer_id;
    $types .= "i";

    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
      $success = "Profile updated successfully!";
       $_SESSION['user_name'] = $name;
      // Refresh $user data after update
      $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
      $stmt->bind_param("i", $freelancer_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $user = $result->fetch_assoc();

      // Redirect after 2 seconds
      header("refresh:2;url=freelancer_dashboard.php");
    } else {
      $message = "Failed to update profile.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Update Profile | WorkNest</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="freelancer_dashboard.css" />
  <style>
    #profilePreview {
      max-width: 150px;
      max-height: 150px;
      border-radius: 50%;
      margin-top: 10px;
      object-fit: cover;
      border: 2px solid #31572c;
    }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="dashboard p-4 shadow rounded">
    <h2 class="mb-4">Update Your Profile</h2>

    <?php if ($message): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?> Redirecting to dashboard...</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
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
        <img id="profilePreview" src="<?= htmlspecialchars($user['profile_image']) ?: 'default_avatar.png' ?>" alt="Profile Preview">
      </div>
      <button type="submit" class="btn btn-primary">Update Profile</button>
      <a href="freelancer_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</div>

<script>
function previewImage(event) {
  const preview = document.getElementById('profilePreview');
  const file = event.target.files[0];
  if (file) {
    preview.src = URL.createObjectURL(file);
  }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
