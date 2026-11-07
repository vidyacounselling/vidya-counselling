
  const logo = document.querySelector('.brand img');
  if (logo) {
    const favicon = document.createElement('link');
    favicon.rel = 'icon';
    favicon.type = 'image/png';
    favicon.href = logo.src;
    document.head.appendChild(favicon);
  }
