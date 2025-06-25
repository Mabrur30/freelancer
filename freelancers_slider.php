<?php
// Include DB config
include 'config.php';

// Fetch freelancers (limit to 6 for slider)
$query = "SELECT id, name, profile_image, bio, role FROM users WHERE role='freelancer' LIMIT 6";
$result = $conn->query($query);

$freelancers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $freelancers[] = $row;
    }
}

// Function to split array into chunks of 3 (for slider grouping)
function chunkFreelancers($array, $size) {
    $chunks = [];
    for ($i = 0; $i < count($array); $i += $size) {
        $chunks[] = array_slice($array, $i, $size);
    }
    return $chunks;
}

$chunks = chunkFreelancers($freelancers, 3);
?>

<section class="freelancers-slider">
  <div class="container">
    <h2>Featured Freelancers</h2>
    <div class="freelancer-slider-track">
      <?php foreach($chunks as $index => $group): ?>
        <div class="freelancer-slide <?php if($index == 0) echo 'active'; ?>">
          <?php foreach($group as $freelancer): ?>
            <div class="freelancer">
              <img src="<?php echo !empty($freelancer['profile_image']) ? htmlspecialchars($freelancer['profile_image']) : 'https://i.pravatar.cc/100?u=' . $freelancer['id']; ?>" alt="<?php echo htmlspecialchars($freelancer['name']); ?>">
              <h4><?php echo htmlspecialchars($freelancer['name']); ?></h4>
              <p><?php echo htmlspecialchars($freelancer['bio'] ?: 'Freelancer'); ?></p>
              <a href="profile.php?id=<?php echo $freelancer['id']; ?>" class="btn-view-profile">View Profile</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="freelancer-controls">
      <span class="freelancer-prev">&laquo;</span>
      <span class="freelancer-next">&raquo;</span>
    </div>
  </div>
</section>
