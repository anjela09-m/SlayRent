<?php include 'templates/header.php'; ?>

<style>
/* Updated Color Palette - Black & Premium Gold Theme */
:root {
  --black: #000000;
  --gold: #FFB627;
  --dark-gold: #CCA43B;
  --white: #ffffff;
  --light-gray: #f8f8f8;
  --dark-gray: #333333;
  --transparent-black: rgba(0, 0, 0, 0.8);
}

/* Hero Section - Keep video as requested */
.hero {
  position: relative;
  width: 100%;
  height: 100vh;
  min-height: 600px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.video-background {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 100%;
  min-width: 100%;
  min-height: 100%;
  transform: translate(-50%, -50%);
  object-fit: cover;
  z-index: -2;
}

.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: -1;
}

.hero-content {
  text-align: center;
  color: white;
  z-index: 1;
  max-width: 800px;
  padding: 0 20px;
}

.hero-content h1 {
  font-size: 3.5rem;
  margin-bottom: 1rem;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
  font-family: Georgia, serif;
}

.hero-content p {
  font-size: 1.3rem;
  margin-bottom: 2rem;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
  font-family: Georgia, serif;
}

/* Featured Section - Black background with gold accents */
.featured-section {
  padding: 80px 20px;
  background-color: var(--black);
  position: relative;
}

.featured-section h2 {
  text-align: center;
  font-size: 2.5rem;
  margin-bottom: 50px;
  color: var(--gold);
  font-weight: bold;
  font-family: Georgia, serif;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.slider-wrapper {
  position: relative;
  max-width: 1300px;
  margin: 0 auto;
  padding: 0 60px;
}

.slider {
  display: flex;
  overflow-x: auto;
  overflow-y: hidden;
  scroll-behavior: smooth;
  gap: 20px;
  padding: 30px 0 40px 0;
  scrollbar-width: none;
  -ms-overflow-style: none;
  scroll-snap-type: x mandatory;
}

.slider::-webkit-scrollbar {
  display: none;
}

.costume-card {
  flex: 0 0 240px;
  height: 340px;
  background: linear-gradient(135deg, var(--black) 0%, var(--dark-gray) 100%);
  background-image: url('assets/images/fixbgcard.jpg');
  background-size: cover;
  background-position: center;
  background-blend-mode: overlay;
  border-radius: 15px;
  padding: 15px;
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
  transition: all 0.4s ease;
  scroll-snap-align: start;
  border: 2px solid var(--gold);
  display: flex;
  flex-direction: column;
  position: relative;
  cursor: pointer;
  transform: rotate(-2deg);
  overflow: hidden;
}

.costume-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.5) 50%, rgba(255,215,0,0.1) 100%);
  z-index: 1;
  pointer-events: none;
}

.costume-card > * {
  position: relative;
  z-index: 2;
}

.costume-card:nth-child(2n) {
  transform: rotate(1deg);
}

.costume-card:nth-child(3n) {
  transform: rotate(-1deg);
}

.costume-card:nth-child(4n) {
  transform: rotate(2deg);
}

.costume-card:hover {
  transform: rotate(0deg) translateY(-15px) scale(1.05);
  box-shadow: 0 15px 40px rgba(255, 215, 0, 0.5);
  border-color: var(--white);
  z-index: 10;
}

.img-box {
  width: 100%;
  height: 180px;
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 12px;
  background: var(--dark-gray);
  border: 2px solid var(--gold);
}

.img-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
  filter: brightness(0.9) contrast(1.1);
}

.costume-card:hover .img-box img {
  transform: scale(1.1);
  filter: brightness(1) contrast(1.2);
}

.costume-card h4 {
  font-size: 1.1rem;
  margin-bottom: 15px;
  color: var(--white);
  font-weight: bold;
  line-height: 1.3;
  flex-grow: 1;
  font-family: Georgia, serif;
  text-align: center;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
}

.costume-card p {
  font-size: 1.1rem;
  color: var(--black);
  font-weight: bold;
  margin-top: auto;
  text-align: center;
  background: linear-gradient(45deg, var(--gold), var(--dark-gold));
  padding: 10px;
  border-radius: 8px;
  font-family: Georgia, serif;
  box-shadow: 0 4px 15px rgba(0,0,0,0.5);
}

.slider-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: linear-gradient(135deg, var(--gold), var(--dark-gold));
  color: var(--black);
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  font-size: 1.3rem;
  cursor: pointer;
  z-index: 10;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--black);
  font-weight: bold;
}

.slider-btn:hover {
  background: linear-gradient(135deg, var(--white), var(--light-gray));
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 6px 20px rgba(255, 215, 0, 0.6);
}

.slider-btn:active {
  transform: translateY(-50%) scale(0.95);
}

.slider-btn.left {
  left: 10px;
}

.slider-btn.right {
  right: 10px;
}

.see-more-wrapper {
  text-align: center;
  margin-top: 50px;
}

.see-more-btn {
  background: linear-gradient(45deg, var(--gold), var(--dark-gold));
  color: var(--black);
  border: none;
  padding: 15px 35px;
  font-size: 1.1rem;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: bold;
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
  font-family: Georgia, serif;
  border: 2px solid var(--black);
}

.see-more-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.6);
  background: linear-gradient(45deg, var(--white), var(--light-gray));
}

/* Modal - Black & Gold Theme */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: var(--black);
  padding: 40px;
  border-radius: 15px;
  text-align: center;
  max-width: 400px;
  margin: 0 20px;
  border: 3px solid var(--gold);
  box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
  position: relative;
}

.modal-content p {
  font-family: Georgia, serif;
  color: var(--white);
  font-size: 1.1rem;
  margin-bottom: 25px;
  font-weight: 300;
}

.close {
  position: absolute;
  right: 15px;
  top: 15px;
  color: var(--gold);
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  transition: color 0.3s ease;
}

.close:hover {
  color: var(--white);
}

.login-btn {
  background: linear-gradient(45deg, var(--gold), var(--dark-gold));
  color: var(--black);
  padding: 12px 25px;
  text-decoration: none;
  border-radius: 20px;
  display: inline-block;
  margin-top: 15px;
  font-family: Georgia, serif;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 3px 10px rgba(255, 215, 0, 0.4);
  border: 2px solid var(--black);
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 215, 0, 0.6);
  background: linear-gradient(45deg, var(--white), var(--light-gray));
}

/* About Section - Black background with gold accents */
.about {
  padding: 80px 20px;
  background: linear-gradient(135deg, var(--black), var(--dark-gray));
}

.about .content {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 60px;
  align-items: center;
}

.about h2 {
  font-size: 2.8rem;
  margin-bottom: 30px;
  color: var(--gold);
  font-family: Georgia, serif;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.about p {
  font-size: 1.1rem;
  line-height: 1.7;
  margin-bottom: 20px;
  color: var(--white);
  font-family: Georgia, serif;
  font-weight: 300;
}

.highlight {
  color: var(--gold);
  font-weight: bold;
}

.tagline {
  font-style: italic;
  color: var(--gold);
  font-size: 1.2rem;
  font-weight: bold;
}

.about .image img {
  width: 100%;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(255, 215, 0, 0.3);
  border: 3px solid var(--gold);
  filter: brightness(0.9) contrast(1.1);
}

/* How It Works - Using cool.jpg as background with black overlay */
.how-it-works {
  padding: 80px 20px;
  background: linear-gradient(rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.85)), 
              url('assets/images/cool.jpg') center/cover;
  background-color: var(--black);
  position: relative;
}

.how-it-works h2 {
  text-align: center;
  font-size: 2.5rem;
  margin-bottom: 50px;
  color: var(--gold);
  font-family: Georgia, serif;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
}

.tabs {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 40px;
}

.tab-btn {
  background: var(--black);
  border: 2px solid var(--gold);
  padding: 15px 25px;
  border-radius: 25px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s ease;
  font-family: Georgia, serif;
  font-weight: bold;
  color: var(--gold);
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.2);
}

.tab-btn.active {
  background: var(--gold);
  color: var(--black);
  border-color: var(--gold);
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
}

.tab-btn:hover:not(.active) {
  background: var(--dark-gray);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 215, 0, 0.3);
  color: var(--white);
}

.steps-box {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
  max-width: 1000px;
  margin: 0 auto;
}

.step-card {
  background: rgba(0, 0, 0, 0.9);
  padding: 30px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0 8px 25px rgba(255, 215, 0, 0.2);
  border: 2px solid var(--gold);
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.step-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 35px rgba(255, 215, 0, 0.4);
  border-color: var(--white);
  background: rgba(0, 0, 0, 0.95);
}

.step-card span {
  font-size: 2rem;
  margin-bottom: 15px;
  display: block;
}

.step-card h3 {
  font-size: 1.3rem;
  margin-bottom: 15px;
  color: var(--gold);
  font-family: Georgia, serif;
  font-weight: bold;
}

.step-card p {
  color: var(--white);
  line-height: 1.5;
  font-family: Georgia, serif;
  font-size: 1rem;
  font-weight: 300;
}

/* Contact - Black & Gold Theme */
.contact {
  padding: 80px 20px;
  text-align: center;
  background: linear-gradient(135deg, var(--black) 0%, var(--dark-gray) 100%);
  border-top: 5px solid var(--gold);
}

.contact h2 {
  font-size: 2.8rem;
  margin-bottom: 40px;
  color: var(--gold);
  font-family: Georgia, serif;
  font-weight: bold;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.contact p {
  font-size: 1.2rem;
  margin-bottom: 20px;
  color: var(--white);
  font-family: Georgia, serif;
  background: rgba(255, 215, 0, 0.1);
  padding: 15px 25px;
  border-radius: 15px;
  display: inline-block;
  margin: 10px;
  box-shadow: 0 4px 15px rgba(255, 215, 0, 0.2);
  border: 2px solid var(--gold);
  transition: all 0.3s ease;
  font-weight: 300;
}

.contact p:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
  background: rgba(255, 215, 0, 0.2);
  border-color: var(--white);
}

.contact a {
  color: var(--gold);
  text-decoration: none;
  font-weight: bold;
  transition: color 0.3s ease;
}

.contact a:hover {
  color: var(--white);
}

/* Responsive */
@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 2.5rem;
  }
  
  .hero-content p {
    font-size: 1.1rem;
  }
  
  .about .content {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  
  .slider-btn {
    display: none;
  }
  
  .tabs {
    flex-direction: column;
    align-items: center;
  }
  
  .tab-btn {
    width: 100%;
    max-width: 350px;
  }
  
  .costume-card {
    flex: 0 0 240px;
  }
  
  .contact p {
    display: block;
    margin: 15px auto;
    max-width: 300px;
  }
}
</style>

<section class="hero">
  <!-- HTML5 Background Video with Fallback -->
  <video autoplay muted loop playsinline class="video-background" poster="assets/images/banner1.jpg">
    <source src="assets/videos/slayrent.mp4" type="video/mp4">
    <!-- Fallback image if video cannot play -->
    <img src="assets/images/banner1.jpg" alt="SlayRent Banner" style="width:100%;height:100%;object-fit:cover;">
    Your browser does not support the video tag.
  </video>

  <!-- Overlay for readability -->
  <div class="overlay"></div>

  <div class="hero-content">
    <h1>Welcome to SlayRent</h1>
    <p>Find the perfect costume for every fest — Onam, Christmas, Cultural and Halloween!</p>
  </div>
</section>

<section class="featured-section">
  <h2>Featured Costumes</h2>

  <div class="slider-wrapper">
    <button class="slider-btn left" onclick="scrollLeft()">❮</button>

    <div class="slider" id="costumeScroll">
      <?php
      include 'includes/config.php';
      $costumes = $conn->query("SELECT * FROM costumes ORDER BY id DESC LIMIT 10");
      while ($c = $costumes->fetch_assoc()): ?>
        <div class="costume-card">
          <div class="img-box">
            <img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['title']) ?>">
          </div>
          <h4><?= htmlspecialchars($c['title']) ?></h4>
          <p>₹<?= $c['price_per_day'] ?>/day</p>
        </div>
      <?php endwhile; ?>
    </div>

    <button class="slider-btn right" onclick="scrollRight()">❯</button>
  </div>

  <div class="see-more-wrapper">
    <button onclick="showLoginPrompt()" class="see-more-btn">✨ See More Costumes</button>
  </div>
</section>

<!-- LOGIN MODAL -->
<div id="loginModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeLoginModal()">&times;</span>
    <p>Please login to view more costumes!</p>
    <a href="login.php" class="login-btn">Login</a>
  </div>
</div>

<section class="about" id="about">
  <div class="content">
    <div class="text">
      <h2>About Us</h2>
      <p>
        At <span class="highlight">SlayRent</span>, we believe college fests are meant to be iconic — without burning your wallet. We're a peer-to-peer costume rental platform that connects students who want to <span class="highlight">borrow</span> with those who are ready to <span class="highlight">lend</span>.
      </p>
      <p>
        From <strong>Onam sarees</strong> to <strong>Halloween costumes</strong>, we've got every vibe covered. Whether you're slaying at Cultural Day or jingling into Christmas, you'll find affordable, stylish outfits just a click away.
      </p>
      <p>
        Why spend thousands for one-time wear? <span class="highlight">Rent. Slay. Repeat.</span>
      </p>
      <p class="tagline">
        We're here to make your college memories ✨ a little more <span class="highlight">aesthetic</span>, <span class="highlight">sustainable</span>, and <span class="highlight">drama-free</span>.
      </p>
    </div>
    <div class="image">
      <img src="assets/images/aboutus.jpg" alt="About SlayRent">
    </div>
  </div>
</section>

<section id="how-it-works" class="how-it-works">
  <h2>How SlayRent Works</h2>

  <div class="tabs">
    <button class="tab-btn active" onclick="showSteps('lend')">How to Lend? 🤝📦🕒</button>
    <button class="tab-btn" onclick="showSteps('borrow')">How to Borrow? 🎭💃🔥</button>
  </div>

  <div id="steps-container">
    <!-- Lend Steps -->
    <div class="steps-box" id="lend-steps">
      <div class="step-card">
        <span>1️⃣</span>
        <h3>Create Your Lender Profile</h3>
        <p>Sign up as a lender and fill in your shop details.</p>
      </div>
      <div class="step-card">
        <span>2️⃣</span>
        <h3>Upload Costumes</h3>
        <p>Add images, descriptions, prices & availability.</p>
      </div>
      <div class="step-card">
        <span>3️⃣</span>
        <h3>Receive Requests</h3>
        <p>Check requests and approve or reject based on availability.</p>
      </div>
    </div>

    <!-- Borrow Steps -->
    <div class="steps-box" id="borrow-steps" style="display: none;">
      <div class="step-card">
        <span>1️⃣</span>
        <h3>Create Your Borrower Profile</h3>
        <p>Register with your name and college ID to start renting.</p>
      </div>
      <div class="step-card">
        <span>2️⃣</span>
        <h3>Browse & Select</h3>
        <p>Search and explore available costumes by category or occasion.</p>
      </div>
      <div class="step-card">
        <span>3️⃣</span>
        <h3>Send Rent Request</h3>
        <p>Choose your favorite costume and send a rent request to the lender.</p>
      </div>
    </div>
  </div>
</section>

<section class="contact">
  <h2>Contact Us</h2>
  <p>Email: <a href="mailto:slayrent@gmail.com">slayrent@gmail.com</a></p>
  <p>Phone: <a href="tel:+1234567890">+1 (234) 567-890</a></p>
  <p>Address: 123 Pink Street, Blossom City</p>
</section>

<script>
function scrollLeft() {
  document.getElementById('costumeScroll').scrollBy({ left: -300, behavior: 'smooth' });
}

function scrollRight() {
  document.getElementById('costumeScroll').scrollBy({ left: 300, behavior: 'smooth' });
}

function showLoginPrompt() {
  document.getElementById('loginModal').style.display = 'flex';
}

function closeLoginModal() {
  document.getElementById('loginModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('loginModal');
  const modalContent = document.querySelector('.modal-content');
  
  // Add click event to all costume cards
  document.querySelectorAll('.costume-card').forEach(card => {
    card.addEventListener('click', function() {
      showLoginPrompt();
    });
  });
  
  // Close modal when clicking outside
  modal.addEventListener('click', function(event) {
    if (event.target === modal) {
      closeLoginModal();
    }
  });
  
  // Prevent modal from closing when clicking inside modal content
  modalContent.addEventListener('click', function(event) {
    event.stopPropagation();
  });
});

function showSteps(type) {
  // Hide all steps
  document.getElementById('lend-steps').style.display = 'none';
  document.getElementById('borrow-steps').style.display = 'none';
  
  // Remove active class from all tabs
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  
  // Show selected steps and mark tab as active
  if (type === 'lend') {
    document.getElementById('lend-steps').style.display = 'grid';
    document.querySelector('[onclick="showSteps(\'lend\')"]').classList.add('active');
  } else {
    document.getElementById('borrow-steps').style.display = 'grid';
    document.querySelector('[onclick="showSteps(\'borrow\')"]').classList.add('active');
  }
}
</script>

<?php include 'templates/footer.php'; ?>
