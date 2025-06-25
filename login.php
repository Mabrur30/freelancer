<?php
session_start();
include 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email.";
    if (!$password) $errors[] = "Password is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                switch ($user['role']) {
                    case 'freelancer':
                        header("Location: freelancer_dashboard.php");
                        break;
                    case 'client':
                        header("Location: client_dashboard.php");
                        break;
                    default:
                        $errors[] = "Invalid user role. Please contact support.";
                }
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Email not registered.";
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login | WorkNest</title>
<link rel="stylesheet" href="auth.css" />
</head>
<body>
<div class="form-container">
  <h2>Login to WorkNest</h2>
  <?php if (isset($_GET['registered'])): ?>
    <div class="success-box">Registration successful! Please login.</div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="error-box">
      <ul>
        <?php foreach($errors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" action="login.php" novalidate>
    <label>Email</label>
    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
    <label>Password</label>
    <input type="password" name="password" required />
    <button type="submit">Login</button>
  </form>
  <p>No account? <a href="register.php">Register here</a></p>
  <p><a href="index.php" class="btn-home">← Back to Home</a></p>

</div>
</body>
</html>
