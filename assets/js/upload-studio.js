(() => {
  const input = document.getElementById('material_file');
  const zone = document.getElementById('upload-dropzone');
  const empty = document.getElementById('drop-empty');
  const selected = document.getElementById('selected-file');
  const name = document.getElementById('selected-file-name');
  const meta = document.getElementById('selected-file-meta');
  const icon = document.getElementById('selected-file-icon');
  const fileError = document.getElementById('file-error');
  const form = document.querySelector('.studio-form');
  const publish = document.getElementById('publish-button');
  if (!input || !zone || !form) return;

  const maxBytes = 10 * 1024 * 1024;
  const allowed = ['pdf', 'jpg', 'jpeg', 'png'];
  const formatSize = (bytes) => bytes < 1024 * 1024
    ? `${Math.max(1, Math.round(bytes / 1024))} KB`
    : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;

  const updateFile = () => {
    const file = input.files && input.files[0];
    input.setCustomValidity('');
    zone.classList.remove('is-invalid');
    fileError.hidden = true;
    if (!file) {
      empty.hidden = false;
      selected.hidden = true;
      zone.classList.remove('has-file');
      return;
    }
    const extension = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
    let message = '';
    if (!allowed.includes(extension)) message = 'Only PDF, JPG and PNG files are supported.';
    else if (file.size < 1) message = 'This file appears to be empty. Choose another file.';
    else if (file.size > maxBytes) message = 'This file is larger than 10 MB. Choose a smaller file.';
    if (message) {
      input.setCustomValidity(message);
      fileError.textContent = message;
      fileError.hidden = false;
      zone.classList.add('is-invalid');
    }
    const iconClass = extension === 'pdf' ? 'bi-file-earmark-pdf' : ['jpg', 'jpeg', 'png'].includes(extension) ? 'bi-file-earmark-image' : 'bi-file-earmark';
    icon.innerHTML = `<i class="bi ${iconClass}" aria-hidden="true"></i>`;
    name.textContent = file.name;
    meta.textContent = `${extension ? extension.toUpperCase() : 'FILE'} · ${formatSize(file.size)}`;
    empty.hidden = true;
    selected.hidden = false;
    zone.classList.add('has-file');
  };

  document.getElementById('browse-files')?.addEventListener('click', (event) => {
    event.stopPropagation();
    input.click();
  });
  document.getElementById('remove-file')?.addEventListener('click', (event) => {
    event.stopPropagation();
    input.value = '';
    updateFile();
    input.focus();
  });
  input.addEventListener('change', updateFile);
  input.addEventListener('focus', () => zone.classList.add('is-focused'));
  input.addEventListener('blur', () => zone.classList.remove('is-focused'));
  zone.addEventListener('click', (event) => {
    if (!event.target.closest('button')) input.click();
  });
  zone.addEventListener('keydown', (event) => {
    if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('button')) {
      event.preventDefault();
      input.click();
    }
  });
  ['dragenter', 'dragover'].forEach((type) => zone.addEventListener(type, (event) => {
    event.preventDefault();
    zone.classList.add('is-dragover');
  }));
  ['dragleave', 'dragend'].forEach((type) => zone.addEventListener(type, (event) => {
    if (!zone.contains(event.relatedTarget)) zone.classList.remove('is-dragover');
  }));
  zone.addEventListener('drop', (event) => {
    event.preventDefault();
    zone.classList.remove('is-dragover');
    if (event.dataTransfer?.files?.length) {
      const transfer = new DataTransfer();
      transfer.items.add(event.dataTransfer.files[0]);
      input.files = transfer.files;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });

  const fields = [...form.querySelectorAll('input[required]:not([type="hidden"]), select[required], textarea[required]')];
  const updateField = (field, showError = false) => {
    if (field !== input && field.tagName !== 'SELECT') {
      field.setCustomValidity(field.required && !field.value.trim() ? 'Please complete this field.' : '');
    }
    const invalid = !field.checkValidity();
    if (field === input) {
      zone.classList.toggle('is-invalid', showError && invalid);
      fileError.textContent = input.validationMessage || 'Choose a PDF, JPG or PNG file up to 10 MB.';
      fileError.hidden = !(showError && invalid);
    } else {
      const group = field.closest('.form-group');
      const feedback = group?.querySelector('.field-error:not(.file-error)');
      field.classList.toggle('is-invalid', showError && invalid);
      field.classList.toggle('is-valid', !invalid && Boolean(field.value));
      if (feedback) feedback.hidden = !(showError && invalid);
    }
    if (showError && invalid) field.setAttribute('aria-invalid', 'true');
    else field.removeAttribute('aria-invalid');
  };
  fields.forEach((field) => {
    field.addEventListener('invalid', () => updateField(field, true));
    field.addEventListener('blur', () => updateField(field, true));
    field.addEventListener('input', () => updateField(field, field.classList.contains('is-invalid')));
    field.addEventListener('change', () => updateField(field, field.classList.contains('is-invalid')));
  });

  const description = document.getElementById('description');
  const counter = document.getElementById('description-count');
  description?.addEventListener('input', () => { counter.textContent = `${description.value.length} / 5000`; });

  form.addEventListener('submit', (event) => {
    fields.forEach((field) => updateField(field, true));
    const firstInvalid = fields.find((field) => !field.checkValidity());
    if (firstInvalid) {
      event.preventDefault();
      firstInvalid.focus();
      return;
    }
    publish.disabled = true;
    publish.classList.add('is-loading');
    publish.setAttribute('aria-busy', 'true');
    publish.querySelector('span').textContent = 'Publishing…';
  });
})();