<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Buddy - Manage Your Studies</title>
  <link rel="stylesheet" href="..\assets\css\styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header>
    <nav>
      <div class="logo">Study Buddy</div>
      <ul>
        <li><a href="#home">Home</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="login.php" class="btn btn-secondary">Login</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <section id="home" class="hero">
      <div class="overlay"></div>
      <div class="content">
        <h1>Welcome to Study Buddy</h1>
        <p class="tagline">Manage your studies effectively and boost productivity!</p>
        <div class="cta-buttons">
          <a href="signup.php" class="btn btn-primary">Sign Up</a>
          <a href="login.php" class="btn btn-secondary">Login</a>
        </div>
      </div>
    </section>

    <section id="features" class="features">
      <h2>Our Features</h2>
      <div class="card-container">
        <div class="card">
          <img src="resource-icon.svg" alt="Resource Management">
          <h3>Resource Management</h3>
          <p>Easily upload, organize, and access your study materials.</p>
        </div>
        <div class="card">
          <img src="collaboration-icon.svg" alt="Collaboration">
          <h3>Collaboration</h3>
          <p>Work together with classmates on projects and assignments.</p>
        </div>
        <div class="card">
          <img src="schedule-icon.svg" alt="Schedule Management">
          <h3>Schedule Management</h3>
          <p>Keep track of your study sessions and deadlines.</p>
        </div>
      </div>
    </section>

    <section id="about" class="about">
      <h2>About Study Buddy</h2>
      <p>Study Buddy is your all-in-one platform for managing your academic life. We provide tools to help you organize your resources, collaborate with peers, and stay on top of your schedule.</p>
      <a href="#" class="btn btn-primary">Learn More</a>
    </section>
  </main>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h4>Contact Us</h4>
        <p>Email: info@studybuddy.com</p>
        <p>Phone: (123) 456-7890</p>
      </div>
      <div class="footer-section">
        <h4>Follow Us</h4>
        <div class="social-icons">
          <a href="#" class="social-icon">FB</a>
          <a href="#" class="social-icon">TW</a>
          <a href="#" class="social-icon">IG</a>
        </div>
      </div>
    </div>
    <div class="copyright">
      &copy; 2023 Study Buddy. All rights reserved.
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
  // Smooth scrolling for navigation links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      document.querySelector(this.getAttribute('href')).scrollIntoView({
        behavior: 'smooth'
      });
    });
  });

  // Add active class to navigation links on scroll
  const sections = document.querySelectorAll('section');
  const navLinks = document.querySelectorAll('nav ul li a');

  window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.clientHeight;
      if (pageYOffset >= sectionTop - sectionHeight / 3) {
        current = section.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      link.classList.remove('active');
      if (link.getAttribute('href').slice(1) === current) {
        link.classList.add('active');
      }
    });
  });

  // Add animation to cards on scroll
  const cards = document.querySelectorAll('.card');
  const animateCards = () => {
    cards.forEach(card => {
      const cardTop = card.getBoundingClientRect().top;
      const cardBottom = card.getBoundingClientRect().bottom;
      if (cardTop < window.innerHeight && cardBottom > 0) {
        card.classList.add('animate');
      } else {
        card.classList.remove('animate');
      }
    });
  };

  window.addEventListener('scroll', animateCards);
  animateCards(); // Initial check on page load
});

  </script>
</body>
</html>