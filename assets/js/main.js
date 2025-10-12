(function(){
  const root = document.documentElement;
  const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  if(savedTheme === 'dark') root.classList.add('dark');

  window.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('themeToggle');
    if(btn){
      btn.addEventListener('click', () => {
        root.classList.toggle('dark');
        const theme = root.classList.contains('dark') ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
      });
    }

    // user menu toggle (supports avatar and label button)
    const um = document.getElementById('userMenu');
    const umBtns = Array.from(document.querySelectorAll('[data-user-menu-trigger="true"]'));
    if (um && umBtns.length) {
      const anyContains = (el) => umBtns.some(b => b.contains(el));
      const close = (e) => {
        if (!um.contains(e.target) && !anyContains(e.target)) um.classList.add('hidden');
      };
      umBtns.forEach(b => b.addEventListener('click', (e) => {
        e.stopPropagation();
        um.classList.toggle('hidden');
      }));
      document.addEventListener('click', close);
    }

    // mobile menu toggle
    const mmBtn = document.getElementById('mobileMenuBtn');
    const mm = document.getElementById('mobileMenu');
    if (mmBtn && mm) {
      const setExpanded = (open) => mmBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      mmBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        mm.classList.toggle('hidden');
        setExpanded(!mm.classList.contains('hidden'));
      });
      document.addEventListener('click', (e) => {
        if (!mm.classList.contains('hidden')) {
          const clickInside = mm.contains(e.target) || mmBtn.contains(e.target);
          if (!clickInside) { mm.classList.add('hidden'); setExpanded(false); }
        }
      });
      // close when a link is clicked
      mm.querySelectorAll('a').forEach(a => a.addEventListener('click', () => { mm.classList.add('hidden'); setExpanded(false); }));
    }
  });

  window.toast = (msg, type='success') => {
    Swal.fire({
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 2500, icon: type, title: msg
    });
  }

  window.apiFetch = async (url, options={}) => {
    const headers = options.headers || {};
    headers['X-Requested-With'] = 'XMLHttpRequest';
    options.headers = headers;
    const res = await fetch(url, options);
    if(!res.ok) throw new Error('Request failed');
    return res.json();
  }
})();
