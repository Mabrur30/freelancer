<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>WorkNest - Freelancing Platform</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <div class="container nav">
      <div class="logo">
        <i class="fas fa-feather-alt"></i>
        <span>WorkNest</span>
      </div>
      <!-- Navbar -->
<nav>
  <a href="index.php">Home</a>
  <a href="browse_jobs.php">Browse Jobs</a>
  <a href="freelancers.php">Freelancers</a>
  <a href="login.php">Login</a>
  <a href="register.php">Register</a>
</nav>

    </div>
  </header>

  <section class="hero">
    <div class="container hero-content">
      <h1>Empower Your Work with WorkNest</h1>
      <p>Find top talent or your next freelance project — all in one place.</p>
<a href="register.php" class="cta">Get Started</a>

    </div>
  </section>

  <section class="how-it-works">
    <div class="container">
      <h2>How WorkNest Works</h2>
      <div class="steps">
        <div class="step">
          <i class="fas fa-user-plus"></i>
          <h3>1. Create Account</h3>
          <p>Sign up as a freelancer or client in seconds.</p>
        </div>
        <div class="step">
          <i class="fas fa-briefcase"></i>
          <h3>2. Post or Apply</h3>
          <p>Clients post jobs, freelancers apply with proposals.</p>
        </div>
        <div class="step">
          <i class="fas fa-handshake"></i>
          <h3>3. Hire & Collaborate</h3>
          <p>Work together, get paid, and grow your career.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="categories">
    <div class="container">
      <h2>Top Categories</h2>
      <div class="category-list">
        <div class="category"><i class="fas fa-code"></i> Development</div>
        <div class="category"><i class="fas fa-paint-brush"></i> Design</div>
        <div class="category"><i class="fas fa-pen-nib"></i> Writing</div>
        <div class="category"><i class="fas fa-chart-line"></i> Marketing</div>
      </div>
    </div>
  </section>

  
<?php include 'freelancers_slider.php'; ?>


  <!-- Testimonials Slider -->
<section class="testimonials-slider">
  <div class="container">
    <h2>What Clients Say</h2>
    <div class="slider">
      <div class="slide active">
        <p>“WorkNest helped me find the perfect developer for my startup. It’s reliable, fast, and intuitive.”</p>
        <strong>— Tanvir Rahman, Startup Founder</strong>
      </div>
      <div class="slide">
        <p>“I hired a designer within hours! WorkNest makes the process seamless and professional.”</p>
        <strong>— Maria Chowdhury, Entrepreneur</strong>
      </div>
      <div class="slide">
        <p>“As a freelancer, I’ve gotten more quality clients through WorkNest than anywhere else.”</p>
        <strong>— Rahim Ahmed, Freelance Developer</strong>
      </div>
    </div>
    <div class="controls">
      <span class="prev">&laquo;</span>
      <span class="next">&raquo;</span>
    </div>
  </div>
</section>


  <section class="cta-banner">
    <div class="container">
      <h2>Join WorkNest Today</h2>
      <p>Whether you’re hiring or looking for work, we’ve got you covered.</p>
     
<a href="register.php" class="cta">Sign Up Now</a>

    </div>
  </section>

  <footer>
    <p>&copy; 2025 WorkNest. All rights reserved.</p>
  </footer>
<script>
  const fSlides = document.querySelectorAll('.freelancer-slide');
  const fPrev = document.querySelector('.freelancer-prev');
  const fNext = document.querySelector('.freelancer-next');
  let fIndex = 0;

  function showFreelancer(index) {
    fSlides[fIndex].classList.remove('active');
    fIndex = (index + fSlides.length) % fSlides.length;
    fSlides[fIndex].classList.add('active');
  }

  fPrev.addEventListener('click', () => showFreelancer(fIndex - 1));
  fNext.addEventListener('click', () => showFreelancer(fIndex + 1));
  setInterval(() => showFreelancer(fIndex + 1), 5000);
</script>

  <script>
  const slides = document.querySelectorAll('.slide');
  const prev = document.querySelector('.prev');
  const next = document.querySelector('.next');
  let current = 0;

  function showSlide(index) {
    slides[current].classList.remove('active');
    current = (index + slides.length) % slides.length;
    slides[current].classList.add('active');
  }

  prev.addEventListener('click', () => showSlide(current - 1));
  next.addEventListener('click', () => showSlide(current + 1));

  setInterval(() => showSlide(current + 1), 5000);
</script>

</body>
</html>
