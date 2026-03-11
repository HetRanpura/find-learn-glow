<?php
// partials/header.php
// Include this at the top of every page AFTER including db.php
$flash = getFlash();
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $pageTitle ?? 'FindLearnGlow – Home Tutor Finder' ?></title>

  <!-- Tailwind CDN (Play CDN for local WAMP) -->
<script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            navy: {
              950: '#04090f',
              900: '#080f1f',
              800: '#0d1528',
              700: '#111d35',
              600: '#1a2744',
              500: '#243356',
            },
            lime: {
              glow: '#a3e635',
            },
            cyan: {
              glow: '#22d3ee',
            }
          },
          fontFamily: {
            display: ['"Space Grotesk"', 'sans-serif'],
            body: ['"DM Sans"', 'sans-serif'],
          },
          boxShadow: {
            lime: '0 0 20px rgba(163,230,53,0.25)',
            cyan: '0 0 20px rgba(34,211,238,0.25)',
            'lime-sm': '0 0 8px rgba(163,230,53,0.4)',
          }
        }
      }
    }
  </script>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

  <style>
    :root {
      --navy-950: #04090f;
      --navy-900: #080f1f;
      --navy-800: #0d1528;
      --navy-700: #111d35;
      --navy-600: #1a2744;
      --lime:  #a3e635;
      --cyan:  #22d3ee;
    }

    * { box-sizing: border-box; }

    body {
      background-color: var(--navy-900);
      color: #e2e8f0;
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
    }

    h1,h2,h3,h4,h5,h6 { font-family: 'Space Grotesk', sans-serif; }

    /* Gradient text utility */
    .gradient-text {
      background: linear-gradient(135deg, var(--lime) 0%, var(--cyan) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Glowing border */
    .border-lime-glow { border: 1px solid rgba(163,230,53,0.35); box-shadow: 0 0 12px rgba(163,230,53,0.15); }
    .border-cyan-glow { border: 1px solid rgba(34,211,238,0.35); box-shadow: 0 0 12px rgba(34,211,238,0.15); }

    /* Neon button */
    .btn-lime {
      background: var(--lime);
      color: #04090f;
      font-weight: 700;
      font-family: 'Space Grotesk', sans-serif;
      letter-spacing: 0.02em;
      transition: box-shadow 0.2s, transform 0.15s;
    }
    .btn-lime:hover {
      box-shadow: 0 0 22px rgba(163,230,53,0.55);
      transform: translateY(-1px);
    }

    .btn-cyan {
      background: transparent;
      border: 1.5px solid var(--cyan);
      color: var(--cyan);
      font-weight: 600;
      font-family: 'Space Grotesk', sans-serif;
      transition: all 0.2s;
    }
    .btn-cyan:hover {
      background: rgba(34,211,238,0.1);
      box-shadow: 0 0 18px rgba(34,211,238,0.35);
    }

    /* Card */
    .card {
      background: var(--navy-800);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 1rem;
      transition: border-color 0.2s, transform 0.2s;
    }
    .card:hover { border-color: rgba(163,230,53,0.2); transform: translateY(-2px); }

    /* Form inputs */
    .form-input {
      background: var(--navy-700);
      border: 1px solid rgba(255,255,255,0.1);
      color: #e2e8f0;
      border-radius: 0.5rem;
      width: 100%;
      padding: 0.625rem 0.875rem;
      outline: none;
      font-size: 0.95rem;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-input:focus {
      border-color: var(--cyan);
      box-shadow: 0 0 0 3px rgba(34,211,238,0.12);
    }
    .form-input::placeholder { color: #4a5568; }

    select.form-input option { background: var(--navy-800); }

    /* Nav active */
    .nav-active { color: var(--lime) !important; }

    /* Badge */
    .badge-lime { background: rgba(163,230,53,0.15); color: var(--lime); border: 1px solid rgba(163,230,53,0.3); }
    .badge-cyan { background: rgba(34,211,238,0.15); color: var(--cyan); border: 1px solid rgba(34,211,238,0.3); }
    .badge-pending { background: rgba(234,179,8,0.15); color: #fbbf24; border: 1px solid rgba(234,179,8,0.3); }

    /* Star rating */
    .star { color: #fbbf24; }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: var(--navy-900); }
    ::-webkit-scrollbar-thumb { background: var(--navy-600); border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(163,230,53,0.4); }

    /* Grid mesh background for hero */
    .mesh-bg {
      background-image:
        linear-gradient(rgba(163,230,53,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(163,230,53,0.04) 1px, transparent 1px);
      background-size: 40px 40px;
    }

    /* Fade-in animation */
    @keyframes fadeUp {
      from { opacity:0; transform:translateY(18px); }
      to   { opacity:1; transform:translateY(0); }
    }
    .fade-up { animation: fadeUp 0.55s ease both; }
    .fade-up-2 { animation: fadeUp 0.55s 0.12s ease both; }
    .fade-up-3 { animation: fadeUp 0.55s 0.24s ease both; }

    /* Pulse glow for CTA */
    @keyframes glowPulse {
      0%,100% { box-shadow: 0 0 12px rgba(163,230,53,0.3); }
      50%      { box-shadow: 0 0 28px rgba(163,230,53,0.55); }
    }
    .glow-pulse { animation: glowPulse 2.5s ease-in-out infinite; }
  </style>
</head>
<body>

<!-- ═══════════════════════════════════════════
     NAVIGATION
═══════════════════════════════════════════ -->
<nav style="background: rgba(8,15,31,0.95); border-bottom: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(12px);" class="sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">

      <!-- Logo -->
      <a href="index.php" class="flex items-center gap-2.5">
        <div style="background: linear-gradient(135deg, var(--lime), var(--cyan)); border-radius: 8px; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#04090f" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <span style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:1.15rem;">
          Find<span class="gradient-text">Learn</span>Glow
        </span>
      </a>

      <!-- Desktop Nav Links -->
      <div class="hidden md:flex items-center gap-6">
        <a href="index.php" class="text-sm font-medium text-slate-400 hover:text-white transition-colors <?= $currentPage==='index.php' ? 'nav-active' : '' ?>">Home</a>
        <a href="tutors.php" class="text-sm font-medium text-slate-400 hover:text-white transition-colors <?= $currentPage==='tutors.php' ? 'nav-active' : '' ?>">Find Tutors</a>
        <?php if ($isLoggedIn): ?>
          <a href="dashboard.php" class="text-sm font-medium text-slate-400 hover:text-white transition-colors <?= $currentPage==='dashboard.php' ? 'nav-active' : '' ?>">Dashboard</a>
        <?php endif; ?>
      </div>

      <!-- Auth Buttons -->
      <div class="flex items-center gap-3">
        <?php if ($isLoggedIn): ?>
          <span class="text-sm text-slate-400 hidden sm:block">Hi, <span style="color:var(--lime);"><?= htmlspecialchars($userName) ?></span></span>
          <a href="logout.php" class="btn-cyan text-sm px-4 py-2 rounded-lg">Logout</a>
        <?php else: ?>
          <a href="login.php" class="btn-cyan text-sm px-4 py-2 rounded-lg">Login</a>
          <a href="register.php" class="btn-lime text-sm px-4 py-2 rounded-lg glow-pulse">Register</a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</nav>

<!-- Flash Message -->
<?php if ($flash): ?>
  <?php
    $bgColor = match($flash['type']) {
      'success' => 'rgba(163,230,53,0.12)',
      'error'   => 'rgba(239,68,68,0.12)',
      'info'    => 'rgba(34,211,238,0.12)',
      default   => 'rgba(255,255,255,0.08)'
    };
    $textColor = match($flash['type']) {
      'success' => 'var(--lime)',
      'error'   => '#f87171',
      'info'    => 'var(--cyan)',
      default   => '#e2e8f0'
    };
    $icon = match($flash['type']) {
      'success' => 'check-circle',
      'error'   => 'x-circle',
      'info'    => 'info',
      default   => 'bell'
    };
  ?>
  <div style="background:<?= $bgColor ?>; border-bottom:1px solid <?= $textColor ?>33;" class="px-4 py-3">
    <div class="max-w-7xl mx-auto flex items-center gap-3">
      <i data-lucide="<?= $icon ?>" style="color:<?= $textColor ?>; width:18px; height:18px; flex-shrink:0;"></i>
      <p style="color:<?= $textColor ?>; font-size:0.9rem;"><?= htmlspecialchars($flash['msg']) ?></p>
    </div>
  </div>
<?php endif; ?>

<!-- Main content wrapper starts here — closed in footer.php -->
<main>