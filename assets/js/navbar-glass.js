/* Lucide-style line icons for existing navigation links. */
(()=>{
 const icons={
  'index.php':'<path d="m3 10 9-7 9 7"/><path d="M5 9v12h14V9"/><path d="M9 21v-7h6v7"/>',
  'browse.php':'<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
  'upload.php':'<path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M20 16.5a4.5 4.5 0 0 0-2.5-8.2A6 6 0 0 0 6 10a4 4 0 0 0 0 8h2"/>',
  'messages.php':'<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/>',
  'favorites.php':'<path d="M6 4h12v17l-6-4-6 4z"/><path d="m12 7 .9 1.8 2 .3-1.5 1.4.4 2-1.8-.9-1.8.9.4-2-1.5-1.4 2-.3z"/>',
  'profile.php':'<circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/>',
  'my_uploads.php':'<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v17H6.5A2.5 2.5 0 0 1 4 17.5z"/><path d="M4 16h16"/><path d="M8 7h8M8 10h6"/>',
  'admin.php':'<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/><path d="m9 12 2 2 4-4"/>',
  'logout.php':'<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>',
  'login.php':'<path d="M14 17l5-5-5-5"/><path d="M19 12H5"/><path d="M12 3H6a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h6"/>',
  'signup.php':'<circle cx="9" cy="8" r="4"/><path d="M3 21a6 6 0 0 1 12 0"/><path d="M19 8v6M16 11h6"/>',
  'admin_materials.php':'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
  'admin_users.php':'<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/>',
  'edit_material.php':'<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>'
 };
 const run=()=>document.querySelectorAll('header nav:not(.mn-pagination) a[href]').forEach(a=>{
  let file='';try{file=new URL(a.href,location.href).pathname.split('/').pop()||'index.php'}catch(_){return}
  const shape=icons[file];if(!shape||a.querySelector('.nav-lucide'))return;
  a.querySelectorAll('i.bi').forEach(i=>i.remove());
  const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');svg.setAttribute('class','nav-lucide');svg.setAttribute('viewBox','0 0 24 24');svg.setAttribute('width','16');svg.setAttribute('height','16');svg.setAttribute('fill','none');svg.setAttribute('stroke','currentColor');svg.setAttribute('stroke-width','1.75');svg.setAttribute('stroke-linecap','round');svg.setAttribute('stroke-linejoin','round');svg.setAttribute('aria-hidden','true');svg.innerHTML=shape;a.insertBefore(svg,a.firstChild);
  if(file===location.pathname.split('/').pop()||(!location.pathname.split('/').pop()&&file==='index.php')){a.classList.add('active');a.setAttribute('aria-current','page')}
  if(file==='logout.php')a.classList.add('nav-signout');
 });
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',run,{once:true});else run();
})();