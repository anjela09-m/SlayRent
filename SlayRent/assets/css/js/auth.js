// Client-side form validation
document.getElementById('login-form')?.addEventListener('submit', function(e) {
  const email = document.getElementById('email').value;
  if (!email.includes('@')) {
    e.preventDefault();
    alert('Please enter a valid email');
  }
});