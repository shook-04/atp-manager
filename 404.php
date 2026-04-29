<?php

http_response_code(404);
require_once 'includes/db.php';
require_once 'includes/auth.php';
$base = BASE_PATH;
$pageTitle = 'Page Not Found';
require_once 'includes/header.php';
?>

<div class="text-center py-24">
    <p class="font-display text-[180px] leading-none text-atp-border select-none">404</p>
    <div class="text-7xl mb-6 animate-bounce">🎾</div>
    <h1 class="font-display text-4xl text-white tracking-wider mb-3">OUT OF BOUNDS</h1>
    <p class="text-gray-400 text-lg max-w-md mx-auto mb-10">
        That page went wide. It doesn't exist, was removed, or maybe you mistyped the URL.
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <?php if (isLoggedIn()): ?>
        <a href="<?= $base ?>/pages/dashboard.php" class="bg-atp-green hover:bg-green-600 text-white font-semibold px-8 py-3.5 rounded-xl transition-colors">Go to Dashboard</a>
        <a href="<?= $base ?>/pages/tournaments.php" class="border border-atp-border text-gray-300 hover:text-white px-8 py-3.5 rounded-xl transition-colors">Browse Tournaments</a>
        <?php else: ?>
        <a href="<?= $base ?>/index.php" class="bg-atp-green hover:bg-green-600 text-white font-semibold px-8 py-3.5 rounded-xl transition-colors">Go to Homepage</a>
        <a href="<?= $base ?>/login.php" class="border border-atp-border text-gray-300 hover:text-white px-8 py-3.5 rounded-xl transition-colors">Log In</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
