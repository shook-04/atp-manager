<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
$base = BASE_PATH;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ATP Manager' : 'ATP Manager' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        atp: {
                            green:  '#00A651',
                            dark:   '#0a0f1a',
                            card:   '#111827',
                            border: '#1f2937',
                            muted:  '#6b7280',
                        }
                    },
                    fontFamily: {
                        display: ['"Bebas Neue"', 'sans-serif'],
                        body:    ['"DM Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #0a0f1a;
            color: #f9fafb;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        
        body.light-mode {
            background-color: #f0f4f8;
            color: #0f172a;
        }

        body.light-mode nav                        { background-color: #ffffff !important; border-color: #cbd5e1 !important; }
        body.light-mode .bg-atp-card               { background-color: #ffffff !important; border-color: #cbd5e1 !important; }
        body.light-mode .bg-atp-dark               { background-color: #f1f5f9 !important; }
        body.light-mode .border-atp-border         { border-color: #cbd5e1 !important; }
        body.light-mode footer                     { border-color: #cbd5e1 !important; }
        body.light-mode .hover\:bg-atp-border:hover { background-color: #e2e8f0 !important; }

        
        body.light-mode .text-white    { color: #0f172a !important; }
        body.light-mode .text-gray-300 { color: #1e293b !important; }
        body.light-mode .text-gray-400 { color: #334155 !important; }
        body.light-mode .text-gray-500 { color: #475569 !important; }
        body.light-mode .text-gray-600 { color: #475569 !important; }

        
        body.light-mode .bg-yellow-500\/20         { background-color: #fef3c7 !important; }
        body.light-mode .text-yellow-300           { color: #78350f !important; }
        body.light-mode .border-yellow-500\/30     { border-color: #d97706 !important; }
        
        body.light-mode .bg-blue-500\/20           { background-color: #dbeafe !important; }
        body.light-mode .text-blue-300             { color: #1e3a8a !important; }
        body.light-mode .border-blue-500\/30       { border-color: #2563eb !important; }
        
        body.light-mode .bg-purple-500\/20         { background-color: #ede9fe !important; }
        body.light-mode .text-purple-300           { color: #4c1d95 !important; }
        body.light-mode .border-purple-500\/30     { border-color: #7c3aed !important; }
        
        body.light-mode .bg-gray-700               { background-color: #e2e8f0 !important; }
        body.light-mode .border-gray-600           { border-color: #94a3b8 !important; }

        
        body.light-mode .bg-green-500\/20          { background-color: #dcfce7 !important; }
        body.light-mode .text-green-300            { color: #14532d !important; }
        body.light-mode .border-green-500\/30      { border-color: #16a34a !important; }
        
        body.light-mode .bg-red-500\/20            { background-color: #fee2e2 !important; }
        body.light-mode .text-red-300              { color: #7f1d1d !important; }
        body.light-mode .border-red-500\/30        { border-color: #dc2626 !important; }
        
        body.light-mode .bg-orange-500\/20         { background-color: #ffedd5 !important; }
        body.light-mode .text-orange-300           { color: #7c2d12 !important; }
        body.light-mode .border-orange-500\/30     { border-color: #ea580c !important; }
       

        
        body.light-mode .bg-atp-green\/20          { background-color: #d1fae5 !important; }
        body.light-mode .border-atp-green\/30      { border-color: #00A651 !important; }

        
        body.light-mode .bg-green-900\/50 { background-color: #dcfce7 !important; }
        body.light-mode .bg-red-900\/50   { background-color: #fee2e2 !important; }
        body.light-mode .bg-blue-900\/50  { background-color: #dbeafe !important; }
        body.light-mode .border-green-700 { border-color: #16a34a !important; }
        body.light-mode .border-red-700   { border-color: #dc2626 !important; }
        body.light-mode .border-blue-700  { border-color: #2563eb !important; }

        
        body.light-mode .border-red-700\/50        { border-color: #dc2626 !important; }
        body.light-mode .text-red-400              { color: #b91c1c !important; }
        body.light-mode .bg-red-900\/40            { background-color: #fee2e2 !important; }
        body.light-mode .border-red-900\/50        { border-color: #dc2626 !important; }

        
        .theme-toggle {
            width: 44px;
            height: 24px;
            background-color: #1f2937;
            border-radius: 9999px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border: 1px solid #374151;
            flex-shrink: 0;
        }
        .theme-toggle.light {
            background-color: #d1fae5;
            border-color: #00A651;
        }
        .theme-toggle-thumb {
            width: 18px;
            height: 18px;
            background-color: #6b7280;
            border-radius: 9999px;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: transform 0.3s ease, background-color 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
        .theme-toggle.light .theme-toggle-thumb {
            transform: translateX(20px);
            background-color: #00A651;
        }
    </style>
</head>
<body class="min-h-screen transition-colors duration-300">


<nav class="bg-atp-card border-b border-atp-border sticky top-0 z-50 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <a href="<?= isLoggedIn() ? "$base/pages/dashboard.php" : "$base/index.php" ?>" class="flex items-center gap-2 flex-shrink-0">
                <span class="font-display text-3xl text-atp-green tracking-wider">ATP</span>
                <span class="font-display text-3xl text-white tracking-wider">Manager</span>
            </a>

            <?php if (isLoggedIn()): ?>
            <div class="hidden md:flex items-center gap-1">
                <a href="<?= $base ?>/pages/dashboard.php"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'dashboard.php' ? 'bg-atp-green text-white' : 'text-gray-300 hover:text-white hover:bg-atp-border' ?>">
                    Dashboard
                </a>
                <a href="<?= $base ?>/pages/tournaments.php"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'tournaments.php' ? 'bg-atp-green text-white' : 'text-gray-300 hover:text-white hover:bg-atp-border' ?>">
                    Tournaments
                </a>
                <a href="<?= $base ?>/pages/my-schedule.php"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'my-schedule.php' ? 'bg-atp-green text-white' : 'text-gray-300 hover:text-white hover:bg-atp-border' ?>">
                    My Schedule
                </a>
                <a href="<?= $base ?>/pages/rankings.php"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $currentPage === 'rankings.php' ? 'bg-atp-green text-white' : 'text-gray-300 hover:text-white hover:bg-atp-border' ?>">
                    Rankings
                </a>
                <?php if ($currentUser['role'] === 'admin'): ?>
                <a href="<?= $base ?>/admin/manage-tournaments.php"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors text-yellow-400 hover:text-yellow-300 hover:bg-atp-border">
                    ⚙ Admin
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="flex items-center gap-3 flex-shrink-0">

                <?php if (isLoggedIn()): ?>
                    <?php if ($currentUser['ranking']): ?>
                    <span class="hidden sm:inline text-xs bg-atp-green/20 text-atp-green border border-atp-green/30 px-2 py-1 rounded-full font-medium">
                        Rank #<?= $currentUser['ranking'] ?>
                    </span>
                    <?php endif; ?>

                    <a href="<?= $base ?>/pages/profile.php" class="text-sm text-gray-300 hover:text-white font-medium transition-colors">
                        <?= htmlspecialchars($currentUser['full_name']) ?>
                    </a>

                    <a href="<?= $base ?>/logout.php" class="text-sm bg-red-900/40 text-red-400 hover:bg-red-900/60 border border-red-900/50 px-3 py-1.5 rounded-lg transition-colors">
                        Logout
                    </a>

                <?php else: ?>
                    <a href="<?= $base ?>/login.php" class="text-sm text-gray-300 hover:text-white transition-colors font-medium">
                        Login
                    </a>

                    <a href="<?= $base ?>/signup.php" class="text-sm bg-atp-green hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors font-medium">
                        Sign Up
                    </a>
                <?php endif; ?>

                <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" title="Toggle dark/light mode" aria-label="Toggle theme">
                    <div class="theme-toggle-thumb">
                        <span id="theme-icon">🌙</span>
                    </div>
                </button>

            </div>

        </div>
    </div>
</nav>

<?php $flash = getFlash(); if ($flash): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
    <div class="rounded-lg px-4 py-3 text-sm font-medium
        <?= $flash['type'] === 'success' ? 'bg-green-900/50 text-green-300 border border-green-700' : '' ?>
        <?= $flash['type'] === 'error'   ? 'bg-red-900/50 text-red-300 border border-red-700'     : '' ?>
        <?= $flash['type'] === 'info'    ? 'bg-blue-900/50 text-blue-300 border border-blue-700'  : '' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<script>
    function toggleTheme() {
        const body   = document.body;
        const toggle = document.getElementById('theme-toggle');
        const icon   = document.getElementById('theme-icon');
        const isLight = body.classList.toggle('light-mode');
        toggle.classList.toggle('light', isLight);
        icon.textContent = isLight ? '☀️' : '🌙';
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const saved  = localStorage.getItem('theme');
        const toggle = document.getElementById('theme-toggle');
        const icon   = document.getElementById('theme-icon');
        if (saved === 'light') {
            document.body.classList.add('light-mode');
            if (toggle) toggle.classList.add('light');
            if (icon)   icon.textContent = '☀️';
        } else {
            if (icon) icon.textContent = '🌙';
        }
    });
</script>
