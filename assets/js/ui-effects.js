(() => {
  const navIcons = {
    'index.php': 'bi-house-door', 'browse.php': 'bi-search', 'upload.php': 'bi-cloud-arrow-up',
    'messages.php': 'bi-chat-dots', 'favorites.php': 'bi-bookmark-heart',
    'my_uploads.php': 'bi-folder2-open', 'profile.php': 'bi-person-circle',
    'admin.php': 'bi-shield-lock', 'login.php': 'bi-box-arrow-in-right',
    'signup.php': 'bi-person-plus', 'logout.php': 'bi-box-arrow-right'
  };
  document.querySelectorAll('header nav a[href]').forEach((link) => {
    const route = link.getAttribute('href').split(/[?#]/, 1)[0].split('/').pop();
    const iconName = navIcons[route];
    if (!iconName || link.querySelector('i.bi, .nav-lucide')) return;
    const icon = document.createElement('i');
    icon.className = `bi ${iconName}`;
    icon.setAttribute('aria-hidden', 'true');
    link.prepend(icon, document.createTextNode(' '));
  });
  const elements = document.querySelectorAll('.section, .stat, .subject-card, .material-card, .step, .mn-panel, .mn-card, .auth-card, .profile-card, .admin-section, .upload-card, .rv-card, .my-card');
  if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const observer = new IntersectionObserver((entries, current) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        current.unobserve(entry.target);
      }
    });
  }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
  elements.forEach((element) => {
    element.classList.add('reveal');
    observer.observe(element);
  });
})();
