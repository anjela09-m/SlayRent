<?php include 'templates/header.php'; ?>

<section class="hero">
  <!-- HTML5 Background Video with Fallback -->
  <video autoplay muted loop playsinline class="video-background" poster="assets/images/banner1.jpg">
    <source src="assets/Video/slayrent.mp4.mp4" type="video/mp4">
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

<br>

<section style="padding: 50px 20px; background: black;">
  <h2 style="text-align:center; color:white; font-size: 32px; margin-bottom: 30px;">Featured Costumes</h2>

  <div class="slider-wrapper">
    <button class="slider-btn left" onclick="scrollLeft()">❮</button>

    <div class="slider" id="costumeScroll">
      <?php
      include 'includes/config.php';
      $costumes = $conn->query("SELECT * FROM costumes ORDER BY id DESC LIMIT 6");
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

  <div style="text-align: center; margin-top: 30px;">
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

<section class="section about" id="about">
  <div class="content">
    <div class="text">
      <h2>About Us</h2>
      <p>
        At <span class="highlight">SlayRent</span>, we believe college fests are meant to be iconic — without burning your wallet. We're a peer-to-peer costume rental platform that connects students who want to <span class="highlight">borrow</span> with those who are ready to <span class="highlight">lend</span> their festive fits.
      </p>
      <p>
        From <strong>Onam sarees</strong> to <strong>Halloween costumes</strong>, we’ve got every vibe covered. Whether you're slaying at Cultural Day or jingling into Christmas, you’ll find affordable, stylish outfits just a click away.
      </p>
      <p>
        Why spend thousands for one-time wear? <span class="highlight">Rent. Slay. Repeat.</span>
      </p>
      <p class="tagline">
        We’re here to make your college memories ✨ a little more <span class="highlight">aesthetic</span>, <span class="highlight">sustainable</span>, and <span class="highlight">drama-free</span>.
      </p>
    </div>
    <div class="image">
      <img src="assets/images/aboutus.jpg" alt="About SlayRent">
    </div>
  </div>
</section>

<!-- HOW TO RENT -->
<section id="how-it-works" style="padding: 60px 20px; background: #fff0f5;">
  <h2 style="text-align: center; font-size: 32px; margin-bottom: 30px;">How SlayRent Works</h2>

  <div style="text-align: center; margin-bottom: 20px;">
    <button class="tab-btn active" onclick="showSteps('lend')">How to Lend?🤝📦🕒</button>
    <button class="tab-btn" onclick="showSteps('borrow')">How to Borrow?🎭💃🔥</button>
  </div>

  <div id="steps-container">
    <!-- Lend Steps -->
    <div class="steps-box" id="lend-steps">
      <div class="step-card">
        <span>1️⃣</span>
        <h3>Create Your Lender Profile🧑‍💻</h3>
        <p>Sign up as a lender and fill in your shop details.</p>
      </div>
      <div class="step-card">
        <span>2️⃣</span>
        <h3>Upload Costumes📷</h3>
        <p>Add images, descriptions, prices & availability.</p>
      </div>
      <div class="step-card">
        <span>3️⃣</span>
        <h3>Receive Requests✅</h3>
        <p>Check requests and approve or reject based on availability.</p>
      </div>
    </div>

    <!-- Borrow Steps -->
    <div class="steps-box" id="borrow-steps" style="display: none;">
      <div class="step-card">
        <span>1️⃣</span>
        <h3>Create Your Borrower Profile🔐</h3>
        <p>Register with your name and college ID to start renting.</p>
      </div>
      <div class="step-card">
        <span>2️⃣</span>
        <h3>Browse & Select🛒</h3>
        <p>Search and explore available costumes by category or occasion.</p>
      </div>
      <div class="step-card">
        <span>3️⃣</span>
        <h3>Send Rent Request🙏</h3>
        <p>Choose your favorite costume and send a rent request to the lender.</p>
      </div>
    </div>
  </div>
</section>

<!-- Add this CSS -->
<style>
  .tab-btn {
    background: #e190ba;
    border: none;
    color: white;
    padding: 12px 25px;
    margin: 0 10px;
    border-radius: 25px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s ease;
  }

  .tab-btn:hover,
  .tab-btn.active {
    background: #c7659e;
  }

  .steps-box {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 30px;
  }

  .step-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    width: 250px;
    padding: 25px;
    text-align: center;
    transition: transform 0.3s ease;
  }

  .step-card:hover {
    transform: scale(1.05);
  }

  .step-card span {
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
  }

  .step-card h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #e190ba;
  }

  .step-card p {
    font-size: 15px;
    color: #555;
  }

  .video-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    z-index: 0;
    pointer-events: none;
    min-width: 100vw;
    min-height: 100vh;
    background: #000;
  }
</style>

<!-- Add this JS -->
<script>
  function showSteps(type) {
    const lend = document.getElementById('lend-steps');
    const borrow = document.getElementById('borrow-steps');
    const buttons = document.querySelectorAll('.tab-btn');

    buttons.forEach(btn => btn.classList.remove('active'));

    if (type === 'lend') {
      lend.style.display = 'flex';
      borrow.style.display = 'none';
      buttons[0].classList.add('active');
    } else {
      lend.style.display = 'none';
      borrow.style.display = 'flex';
      buttons[1].classList.add('active');
    }
  }
</script>

<section class="contact">
  <h2>Contact Us</h2>
  <p>Email: <a href="mailto:info@example.com">slayrent@gmail.com</a></p>
  <p>Phone: <a href="tel:+1234567890">+1 (234) 567-890</a></p>
  <p>Address: 123 Pink Street, Blossom City</p>
</section>

<!-- JAVASCRIPT -->
<script>
  function scrollLeft() {
    document.getElementById('costumeScroll').scrollLeft -= 250;
  }

  function scrollRight() {
    document.getElementById('costumeScroll').scrollLeft += 250;
  }

  function showLoginPrompt() {
    document.getElementById('loginModal').style.display = 'block';
  }

  function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
  }

  window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target == modal) {
      modal.style.display = 'none';
    }
  }
  
function showSteps(type) {
    const lend = document.getElementById('lend-steps');
    const borrow = document.getElementById('borrow-steps');
    const buttons = document.querySelectorAll('.tab-btn');

    buttons.forEach(btn => btn.classList.remove('active'));

    if (type === 'lend') {
      lend.style.display = 'flex';
      borrow.style.display = 'none';
      buttons[0].classList.add('active');
    } else {
      lend.style.display = 'none';
      borrow.style.display = 'flex';
      buttons[1].classList.add('active');
    }
  }
</script>

<!-- CSS -->
<style>
  .slider-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
  }

  .slider {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 20px 0;
  }

  .costume-card {
    min-width: 220px;
    background: #111;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(255, 255, 255, 0.05);
    padding: 15px;
    text-align: center;
    transition: transform 0.3s;
    color: white;
  }

  .costume-card:hover {
    transform: scale(1.05);
  }

  .img-box {
    border-radius: 12px;
    overflow: hidden;
    height: 220px;
    margin-bottom: 10px;
  }

  .costume-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 0.4s ease-in-out;
  }

  .slider-btn {
    background-color: white;
    color: black;
    border: none;
    padding: 12px 16px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    z-index: 2;
    transition: background 0.3s;
  }

  .slider-btn:hover {
    background-color: white;
  }

  .see-more-btn {
    background-color: white;
    color: black;
    padding: 12px 28px;
    font-size: 16px;
    border-radius: 30px;
    border: none;
    cursor: pointer;
    transition: 0.3s ease;
  }

  .see-more-btn:hover {
    background-color: white;
  }

  /* MODAL STYLING */
  .modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 30px;
    border: 2px solid #e190ba;
    border-radius: 12px;
    width: 300px;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    position: relative;
    animation: fadeIn 0.4s;
  }

  .close {
    color: #aaa;
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
  }

  .close:hover {
    color: #e190ba;
  }

  .login-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #e190ba;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s;
  }

  .login-btn:hover {
    background-color: #c96ea0;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
  .tab-btn {
    background: #e190ba;
    border: none;
    color: white;
    padding: 12px 25px;
    margin: 0 10px;
    border-radius: 25px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s ease;
  }

  .tab-btn:hover,
  .tab-btn.active {
    background: #c7659e;
  }

  .steps-box {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 30px;
  }

  .step-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    width: 250px;
    padding: 25px;
    text-align: center;
    transition: transform 0.3s ease;
  }

  .step-card:hover {
    transform: scale(1.05);
  }

  .step-card span {
    font-size: 30px;
    display: block;
    margin-bottom: 10px;
  }

  .step-card h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #e190ba;
  }

  .step-card p {
    font-size: 15px;
    color: #555;
  }

  .video-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    z-index: 0;
    pointer-events: none;
    min-width: 100vw;
    min-height: 100vh;
    background: #000;
  }
</style>


<?php include 'templates/footer.php'; ?>
