<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/settings.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars(app_name()) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="<?= base_url() ?>/assets/images/logo.png" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              DEFAULT: '#2563EB',
              dark: '#1E40AF'
            },
            secondary: '#0B132B',
            highlight: '#7C3AED'
          },
          fontFamily: {
            sans: ['Inter', 'Poppins', 'system-ui', 'sans-serif']
          }
        }
      },
      darkMode: 'class'
    }
  </script>
  <link rel="stylesheet" href="<?= base_url() ?>/assets/css/styles.css" />
  <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script defer src="<?= base_url() ?>/assets/js/main.js"></script>
  <script>
    // Defensive: if a host-injected overlay sits above the header, disable its pointer events
    window.addEventListener('load', function(){
      try {
        var header = document.querySelector('header');
        if(!header) return;
        var pts = [ [10,10], [Math.max(10, window.innerWidth-10), 10], [Math.max(10, Math.floor(window.innerWidth/2)), 10] ];
        pts.forEach(function(p){
          var el = document.elementFromPoint(p[0], p[1]);
          if(el && el !== header && !header.contains(el)){
            var cs = window.getComputedStyle(el);
            var zi = parseInt(cs.zIndex || '0', 10) || 0;
            if((cs.position === 'fixed' || cs.position === 'absolute' || cs.position === 'sticky') && zi >= 1000){
              el.style.pointerEvents = 'none';
            }
          }
        });
      } catch(e) { /* no-op */ }
    });
  </script>
</head>
<body class="grid-bg app-bg text-gray-900 dark:text-gray-100 transition-colors duration-300">
