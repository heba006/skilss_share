(() => {
  const iconByGlyph = {
    '🎓': 'school', '📚': 'book', '📖': 'book', '📄': 'file', '📤': 'upload',
    '⭐': 'star', '❤️': 'heart', '❤': 'heart', '💗': 'heart', '💜': 'heart', '🤍': 'heart',
    '💬': 'message', '🔎': 'search', '⌕': 'search', '👥': 'users', '👑': 'shield',
    '🗑️': 'trash', '🗑': 'trash', '📭': 'inbox', '🖼️': 'image', '🖼': 'image',
    '✏️': 'edit', '✏': 'edit', '💾': 'save', '✅': 'check', '✓': 'check', '⚠️': 'warning', '⚠': 'warning',
    '📂': 'folder', '📅': 'calendar', '👁': 'eye', '✉': 'mail', '👤': 'user',
    '⚡': 'sparkle', '☁️': 'cloud', '☁': 'cloud', '🔐': 'lock', '👋': 'user', '♡': 'heart', '♥': 'heart',
    '✦': 'sparkle', '✳': 'sparkle', '＋': 'plus', '✎': 'edit', '↗': 'external',
    '→': 'arrow-right', '←': 'arrow-left'
  };
  const iconKeys = Object.keys(iconByGlyph).sort((a, b) => b.length - a.length);
  const iconPattern = new RegExp(iconKeys.map(glyph => glyph.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\uFE0F?').join('|'), 'gu');
  const sprite = 'assets/images/knowledge-icons.svg#';
  const ignoredTags = new Set(['SCRIPT', 'STYLE', 'TEXTAREA', 'SVG', 'NOSCRIPT']);

  function iconNode(glyph) {
    const span = document.createElement('span');
    span.className = 'sk-icon-token';
    span.setAttribute('aria-hidden', 'true');
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('focusable', 'false');
    const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
    use.setAttribute('href', sprite + iconByGlyph[glyph]);
    svg.appendChild(use);
    span.appendChild(svg);
    return span;
  }

  function unifyTextIcons() {
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
      acceptNode(node) {
        const parent = node.parentElement;
        if (!parent || ignoredTags.has(parent.tagName) || !iconPattern.test(node.nodeValue)) return NodeFilter.FILTER_REJECT;
        iconPattern.lastIndex = 0;
        return NodeFilter.FILTER_ACCEPT;
      }
    });
    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach(node => {
      const value = node.nodeValue;
      iconPattern.lastIndex = 0;
      let cursor = 0;
      let match;
      const fragment = document.createDocumentFragment();
      while ((match = iconPattern.exec(value))) {
        if (match.index > cursor) fragment.append(value.slice(cursor, match.index));
        const glyph = match[0].replace(/\uFE0F/g, '');
        if (iconByGlyph[glyph]) fragment.append(iconNode(glyph));
        else fragment.append(match[0]);
        cursor = match.index + match[0].length;
      }
      if (cursor < value.length) fragment.append(value.slice(cursor));
      node.replaceWith(fragment);
    });
  }

  function unifyNavigation() {
    const nav = document.querySelector('header nav[aria-label="Main navigation"], header nav');
    if (!nav) return;
    const current = location.pathname.split('/').pop() || 'index.php';
    if (current === 'index.php' && !document.querySelector('header .auth a[href="logout.php"]')) return;
    const hasLogout = Boolean(document.querySelector('header a[href="logout.php"]'));
    const isAuthenticated = hasLogout || !document.querySelector('header a[href="login.php"], header a[href="signup.php"]');
    const baseLinks = isAuthenticated ? [
      ['index.php', 'Home'], ['browse.php', 'Browse'], ['upload.php', 'Upload'],
      ['my_uploads.php', 'My uploads'], ['messages.php', 'Messages'],
      ['favorites.php', 'Favorites'], ['profile.php', 'My profile']
    ] : [['index.php', 'Home'], ['browse.php', 'Browse'], ['login.php', 'Log in'], ['signup.php', 'Create account']];
    const existing = new Map([...nav.querySelectorAll('a[href]')].map(link => [link.getAttribute('href').split('?')[0], link]));
    const fragment = document.createDocumentFragment();
    baseLinks.forEach(([href, label]) => {
      const link = existing.get(href) || document.createElement('a');
      link.href = href;
      const badge = link.querySelector('.unread-badge');
      link.textContent = label;
      if (badge) link.append(' ', badge);
      link.classList.add('sk-nav-link');
      const current = location.pathname.split('/').pop() || 'index.php';
      const isCurrent = current === href || (href === 'admin.php' && current.startsWith('admin_'));
      link.classList.toggle('active', isCurrent);
      link.classList.toggle('current', isCurrent);
      if (isCurrent) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
      fragment.append(link);
    });
    if (existing.has('logout.php')) fragment.append(existing.get('logout.php'));
    else if (isAuthenticated && !hasLogout) {
      const logout = document.createElement('a');
      logout.href = 'logout.php';
      logout.textContent = 'Log out';
      logout.className = 'sk-nav-link';
      fragment.append(logout);
    }
    [...existing.entries()].filter(([href]) => href.startsWith('admin')).forEach(([href, link]) => {
      const isCurrentAdmin = href === current || (current.startsWith('admin_') && href === 'admin.php');
      link.classList.add('sk-nav-link');
      link.classList.toggle('active', isCurrentAdmin);
      if (isCurrentAdmin) link.setAttribute('aria-current', 'page');
      fragment.append(link);
    });
    nav.replaceChildren(fragment);
    nav.classList.add('sk-workflow-nav');
    document.querySelectorAll('a[href^="view_material.php"][target="_blank"]').forEach(link => link.target = '_self');
  }

  function init() {
    unifyTextIcons();
    unifyNavigation();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();