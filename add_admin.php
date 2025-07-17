<?php
include 'includes/config.php';

$name = "Admin User";  // Your name
$email = "annapadiyara123@gmail.com";  // Your admin email
$password = password_hash("slayyy123", PASSWORD_DEFAULT);  // Secure password

$stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);
$stmt->execute();

echo "✅ Admin added successfully!";
?> 