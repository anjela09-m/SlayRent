<?php include 'templates/header.php'; ?>

<section class="hero">
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

  <div style="text-align: center; margin-top: 30px;">
    <button onclick="showLoginPrompt()" class="see-more-btn">✨ See More Costumes</button>
  </div>
</section>

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
<section class="section how" id="howtorent">
  <h2 class="center-heading">How to Rent in 4 Easy Steps</h2>
  <div class="steps">
    <div class="step">
      <span>1️⃣</span>
      <p>Register as a borrower or lender</p>
    </div>
    <div class="step">
      <span>2️⃣</span>
      <p>Browse or upload costumes</p>
    </div>
    <div class="step">
      <span>3️⃣</span>
      <p>Confirm and pay via UPI</p>
    </div>
    <div class="step">
      <span>4️⃣</span>
      <p>Pick up & SLAY your fest look!</p>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section class="section contact" id="contact">
  <h2>Let's Connect</h2>
  <p>Email us at <a href="mailto:slayrent@gmail.com">slayrent@gmail.com</a></p>
  <p>Follow us on <a href="https://instagram.com/slay.rent" target="_blank">@slay.rent</a></p>
</section>

<script>
  function scrollLeft() {
    document.getElementById('costumeScroll').scrollLeft -= 250;
  }

  function scrollRight() {
    document.getElementById('costumeScroll').scrollLeft += 250;
  }

  function showLoginPrompt() {
    if (confirm("Please login or register to view full costume listings.")) {
      window.location.href = 'login.php?redirect=view_costumes.php';
    }
  }
</script>

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
    background-color:white;
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
</style>

<?php include 'templates/footer.php'; ?>
