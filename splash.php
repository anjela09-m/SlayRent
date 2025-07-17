<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome to SlayRent</title>
  <style>
    body {
      margin: 0;
      background-color: #f9f9f9; /* Softer background */
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
      font-family: Arial, sans-serif;
    }

    .logo-container {
      animation: zoomFade 2s ease forwards;
    }

    @keyframes zoomFade {
      0% {
        transform: scale(0);
        opacity: 0;
      }
      60% {
        transform: scale(1.2);
        opacity: 1;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }

    .logo-container img {
      width: 180px; 
      max-width: 80%; 
      height: auto;
    }
  </style>
  <script>
    // Redirect after 3 seconds to index.php
    setTimeout(function() {
      window.location.href = 'index.php';
    }, 3000);
  </script>
</head>
<body>
  <div class="logo-container">
    <img src="logo.png" alt="SlayRent Logo">
  </div>
</body>
</html>
