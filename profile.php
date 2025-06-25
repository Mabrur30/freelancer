<?php
include 'config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No profile specified.";
    exit;
}

$id = intval($_GET['id']);

// Fetch freelancer info
$sql = "SELECT id, name, email, profile_image, bio, role FROM users WHERE id = ? AND role = 'freelancer'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Freelancer not found.";
    exit;
}

$freelancer = $result->fetch_assoc();

// Fetch portfolio items
$sql_portfolio = "SELECT title, description, image_url, project_url FROM portfolio WHERE freelancer_id = ?";
$stmt_port = $conn->prepare($sql_portfolio);
$stmt_port->bind_param("i", $id);
$stmt_port->execute();
$portfolio_result = $stmt_port->get_result();

$portfolio = [];
while ($row = $portfolio_result->fetch_assoc()) {
    $portfolio[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo htmlspecialchars($freelancer['name']); ?> - Profile | WorkNest</title>
  <link rel="stylesheet" href="profile.css" />
</head>
<body>

<div class="profile-container">
  <div class="profile-header">
    <img src="<?php echo !empty($freelancer['profile_image']) ? htmlspecialchars($freelancer['profile_image']) : 'https://i.pravatar.cc/150?u=' . $freelancer['id']; ?>" alt="<?php echo htmlspecialchars($freelancer['name']); ?>" class="profile-image" />
    <div class="profile-info">
      <h1><?php echo htmlspecialchars($freelancer['name']); ?></h1>
      <p class="bio"><?php echo htmlspecialchars($freelancer['bio'] ?: 'Freelancer at WorkNest'); ?></p>
    </div>
  </div>

  <section class="portfolio-section">
    <h2>Portfolio</h2>
    <?php if (count($portfolio) === 0): ?>
      <p>No portfolio items added yet.</p>
    <?php else: ?>
      <div class="portfolio-grid">
        <?php foreach ($portfolio as $item): ?>
          <div class="portfolio-item">
            <?php if (!empty($item['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" />
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
            <p><?php echo htmlspecialchars($item['description']); ?></p>
            <?php if (!empty($item['project_url'])): ?>
              <a href="<?php echo htmlspecialchars($item['project_url']); ?>" target="_blank" class="project-link">View Project</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <a href="freelancer_dashboard.php" class="back-link">← Back to Home</a>
</div>

</body>
</html>
