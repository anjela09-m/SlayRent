// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
  const menuBtn = document.querySelector('.menu-btn');
  const mobileMenu = document.querySelector('.mobile-menu');
  
  menuBtn?.addEventListener('click', () => {
    mobileMenu.classList.toggle('active');
  });
});