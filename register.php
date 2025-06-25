<?php
include 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$name) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if (!in_array($role, ['freelancer', 'client'])) $errors[] = "Select a valid role.";

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Email already registered.";
    $stmt->close();

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $errors[] = "Database error. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Register | WorkNest</title>
<link rel="stylesheet" href="auth.css" />
</head>
<body>
<div class="form-container">
  <h2>Create an Account</h2>
  <?php if (!empty($errors)): ?>
    <div class="error-box">
      <ul>
        <?php foreach($errors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" action="register.php" novalidate>
    <label>Name</label>
    <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" />
    <label>Email</label>
    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
    <label>Password</label>
    <input type="password" name="password" required />
    <label>Confirm Password</label>
    <input type="password" name="confirm_password" required />
    <label>Role</label>
    <select name="role" required>
      <option value="">Select Role</option>
      <option value="freelancer" <?php if (($_POST['role'] ?? '')==='freelancer') echo 'selected'; ?>>Freelancer</option>
      <option value="client" <?php if (($_POST['role'] ?? '')==='client') echo 'selected'; ?>>Client</option>
    </select>
    <button type="submit">Register</button>
  </form>
  <p>Already have an account? <a href="login.php">Login here</a></p>
  <p><a href="index.php" class="btn-home">← Back to Home</a></p>

</div>
</body>
</html>
