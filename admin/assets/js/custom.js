// Custom JavaScript for Admin Dashboard

// Profile Dropdown Toggle
document.addEventListener('DOMContentLoaded', function() {
  const profileDropdown = document.getElementById('profileDropdown');
  const dropdownMenu = document.getElementById('dropdownMenu');

  if (profileDropdown && dropdownMenu) {
    // Toggle dropdown when the button is clicked
    profileDropdown.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation(); // Prevent click from bubbling to document
      dropdownMenu.classList.toggle('show');
      dropdownMenu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (dropdownMenu.classList.contains('show') && 
          !profileDropdown.contains(e.target) && 
          !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
        dropdownMenu.classList.add('hidden');
      }
    });

    // Close dropdown on ESC key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
        dropdownMenu.classList.remove('show');
        dropdownMenu.classList.add('hidden');
      }
    });
  }

  // Make sure all navigation links work correctly
  const navLinks = document.querySelectorAll('a.menu-item');
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // Allow navigation to happen naturally
      // This will ensure logout and other links work properly
    });
  });

  // Initialize all tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  if (typeof bootstrap !== 'undefined') {
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
});
