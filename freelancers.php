<?php
session_start();
include 'config.php';

// Fetch all freelancers
$sql = "
  SELECT u.id, u.name, u.profile_image, u.bio,
         IFNULL(AVG(r.rating), 0) AS avg_rating,
         COUNT(r.id) AS total_reviews
  FROM users u
  LEFT JOIN review r ON u.id = r.reviewed_id
  WHERE u.role = 'freelancer'
  GROUP BY u.id
  ORDER BY avg_rating DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Freelancers | WorkNest</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .freelancer-card {
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 16px;
      text-align: center;
      transition: 0.3s;
    }
    .freelancer-card:hover {
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .freelancer-card img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Explore Freelancers</h2>
  <div class="row g-4">
    <?php while ($freelancer = $result->fetch_assoc()): ?>
      <div class="col-md-4">
        <div class="freelancer-card">
          <img src="<?= htmlspecialchars($freelancer['profile_image'] ?: 'default_avatar.png') ?>" alt="Profile" />
          <h5><?= htmlspecialchars($freelancer['name']) ?></h5>
          <p><?= htmlspecialchars(substr($freelancer['bio'], 0, 60)) ?>...</p>
          <p>
            <i class="fas fa-star text-warning"></i>
            <?= number_format($freelancer['avg_rating'], 1) ?> (<?= $freelancer['total_reviews'] ?> reviews)
          </p>
          <a href="profile.php?id=<?= $freelancer['id'] ?>" class="btn btn-sm btn-outline-success">
            View Profile
          </a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>
</body>
</html>
