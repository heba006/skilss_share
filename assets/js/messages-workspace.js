(() => {
  const search = document.getElementById('studentSearch');
  const rows = [...document.querySelectorAll('.student-link')];
  const noResults = document.getElementById('searchEmpty');
  const messages = document.getElementById('chatMessages');
  const form = document.getElementById('chatForm');
  const input = document.getElementById('messageInput');
  const send = document.getElementById('sendButton');

  const filterStudents = () => {
    const query = (search?.value || '').trim().toLocaleLowerCase();
    let visible = 0;
    rows.forEach((row) => {
      const text = (row.dataset.search || row.textContent).toLocaleLowerCase();
      const match = !query || text.includes(query);
      row.hidden = !match;
      if (match) visible += 1;
    });
    if (noResults) noResults.hidden = visible > 0;
  };

  search?.addEventListener('input', filterStudents);
  if (messages) messages.scrollTop = messages.scrollHeight;

  if (input) {
    const resize = () => {
      input.style.height = 'auto';
      input.style.height = `${Math.min(input.scrollHeight, 120)}px`;
    };
    input.addEventListener('input', resize);
    input.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
        event.preventDefault();
        if (input.value.trim()) form?.requestSubmit();
      }
    });
  }

  form?.addEventListener('submit', (event) => {
    if (!input?.value.trim()) {
      event.preventDefault();
      input?.focus();
      return;
    }
    if (send?.disabled) {
      event.preventDefault();
      return;
    }
    if (send) {
      send.disabled = true;
      send.classList.add('is-sending');
      send.setAttribute('aria-busy', 'true');
      send.querySelector('span').textContent = 'Sending…';
    }
  });
})();