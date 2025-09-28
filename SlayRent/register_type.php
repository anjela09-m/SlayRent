<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register | SlayRent</title>
  
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Times+New+Roman&display=swap');
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #BDB93 0%, #BE5B50 50%, #8A2D3B 100%);
      font-family: 'Times New Roman', serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .form-container {
      background: white;
      max-width: 480px;
      width: 100%;
      padding: 50px 40px;
      border-radius: 25px;
      box-shadow: 0 25px 50px rgba(100, 27, 46, 0.3);
      text-align: center;
      position: relative;
      overflow: hidden;
      border: 3px solid #BE5B50;
    }

    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #BE5B50, #8A2D3B, #641B2E, #BE5B50);
      background-size: 200% 100%;
      animation: gradientShift 3s ease-in-out infinite;
    }

    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .form-container h2 {
      font-family: 'Candara', sans-serif;
      font-size: 2.6rem;
      color: #641B2E;
      margin-bottom: 20px;
      font-weight: 700;
      text-shadow: 2px 2px 4px rgba(100, 27, 46, 0.1);
    }

    .subtitle {
      font-family: 'Times New Roman', serif;
      font-size: 1.3rem;
      color: #8A2D3B;
      margin-bottom: 45px;
      font-weight: 400;
      line-height: 1.5;
    }

    .role-buttons {
      display: flex;
      flex-direction: column;
      gap: 25px;
      margin-top: 30px;
    }

    .role-btn {
      display: block;
      background: linear-gradient(135deg, #BE5B50 0%, #8A2D3B 100%);
      color: white;
      padding: 25px 35px;
      border-radius: 18px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1.2rem;
      font-family: 'Candara', sans-serif;
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 10px 30px rgba(190, 91, 80, 0.4);
      border: 2px solid transparent;
    }

    .role-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: left 0.6s ease;
    }

    .role-btn:hover::before {
      left: 100%;
    }

    .role-btn:hover {
      background: linear-gradient(135deg, #8A2D3B 0%, #641B2E 100%);
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(138, 45, 59, 0.6);
      border-color: #641B2E;
    }

    .role-btn:active {
      transform: translateY(-4px);
      box-shadow: 0 15px 35px rgba(138, 45, 59, 0.5);
    }

    /* Add icons to buttons */
    .role-btn:first-child::after {
      content: '🏪';
      position: absolute;
      right: 25px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.8rem;
      filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.3));
    }

    .role-btn:last-child::after {
      content: '🎓';
      position: absolute;
      right: 25px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.8rem;
      filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.3));
    }

    /* Enhanced button text styling */
    .role-btn {
      position: relative;
      z-index: 1;
    }

    .role-btn span {
      position: relative;
      z-index: 2;
      display: block;
    }

    /* Add loading state */
    .role-btn.loading {
      pointer-events: none;
      opacity: 0.8;
      background: linear-gradient(135deg, #641B2E 0%, #8A2D3B 100%);
    }

    .role-btn.loading::after {
      content: '⏳';
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      from { transform: translateY(-50%) rotate(0deg); }
      to { transform: translateY(-50%) rotate(360deg); }
    }

    /* Container animations */
    .form-container {
      animation: slideIn 0.8s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(40px) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    /* Hover effect for the container */
    .form-container:hover {
      transform: translateY(-3px);
      box-shadow: 0 30px 60px rgba(100, 27, 46, 0.4);
      transition: all 0.4s ease;
    }

    /* Add a subtle pattern overlay */
    .form-container::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: radial-gradient(circle at 25px 25px, rgba(190, 91, 80, 0.08) 1px, transparent 1px);
      background-size: 50px 50px;
      pointer-events: none;
      z-index: -1;
    }

    /* Responsive design */
    @media (max-width: 480px) {
      .form-container {
        padding: 40px 30px;
        margin: 20px;
        border-radius: 20px;
      }

      .form-container h2 {
        font-size: 2.2rem;
      }

      .subtitle {
        font-size: 1.1rem;
      }

      .role-btn {
        padding: 20px 25px;
        font-size: 1.1rem;
        border-radius: 15px;
      }

      .role-btn:first-child::after,
      .role-btn:last-child::after {
        right: 20px;
        font-size: 1.5rem;
      }
    }

    /* Tablet styles */
    @media (min-width: 481px) and (max-width: 768px) {
      .role-buttons {
        flex-direction: row;
        gap: 20px;
      }

      .role-btn {
        flex: 1;
        padding: 28px 25px;
        font-size: 1.1rem;
      }

      .role-btn:first-child::after,
      .role-btn:last-child::after {
        right: 20px;
        font-size: 1.6rem;
      }
    }

    /* Add subtle glow effect */
    .role-btn {
      box-shadow: 
        0 10px 30px rgba(190, 91, 80, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    }

    .role-btn:hover {
      box-shadow: 
        0 20px 40px rgba(138, 45, 59, 0.6),
        inset 0 1px 0 rgba(255, 255, 255, 0.3),
        0 0 20px rgba(190, 91, 80, 0.3);
    }

    /* Enhanced focus states for accessibility */
    .role-btn:focus {
      outline: 3px solid #BDB93;
      outline-offset: 2px;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Join SlayRent</h2>
    <p class="subtitle">Are you a lender or a borrower?</p>
    
    <div class="role-buttons">
      <a href="register.php?type=lender" class="role-btn">
        <span>I am a Lender</span>
      </a>
      <a href="register.php?type=borrower" class="role-btn">
        <span>I am a Borrower</span>
      </a>
    </div>
  </div>

  <script>
    // Add click feedback
    document.querySelectorAll('.role-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        this.classList.add('loading');
        // Remove loading after navigation starts
        setTimeout(() => {
          this.classList.remove('loading');
        }, 100);
      });
    });

    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
      const buttons = document.querySelectorAll('.role-btn');
      if (e.key === '1') {
        buttons[0].click();
      } else if (e.key === '2') {
        buttons[1].click();
      }
    });

    // Add subtle mouse tracking effect
    document.addEventListener('mousemove', function(e) {
      const container = document.querySelector('.form-container');
      const rect = container.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const rotateX = (y - centerY) / 20;
      const rotateY = (centerX - x) / 20;
      
      container.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-3px)`;
    });

    // Reset transform when mouse leaves
    document.addEventListener('mouseleave', function() {
      const container = document.querySelector('.form-container');
      container.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
    });
  </script>
</body>
</html>
