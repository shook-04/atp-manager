<?php
// index.php — Home / Landing Page
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('/pages/dashboard.php');

$pageTitle = 'Welcome';
require_once 'includes/header.php';
?>

<section class="text-center py-20 px-4">
    <h1 class="font-display text-7xl sm:text-9xl text-white leading-none tracking-wider mb-4">
        YOUR SEASON.<br>
        <span class="text-atp-green">YOUR WAY.</span>
    </h1>
    <p class="text-gray-400 text-lg sm:text-xl max-w-2xl mx-auto mt-6 mb-10 leading-relaxed">
        The ATP Tour features 60+ tournaments across 29 countries. ATP Manager puts
        the entire calendar in your hands — so you can focus on playing, not planning.
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="<?= BASE_PATH ?>/signup.php"
           class="bg-atp-green hover:bg-green-600 text-white font-semibold text-lg px-10 py-4 rounded-xl transition-all duration-200 hover:scale-105">
            Get Started — It's Free
        </a>
        <a href="<?= BASE_PATH ?>/login.php"
           class="border border-atp-border text-gray-300 hover:text-white hover:border-gray-500 font-semibold text-lg px-10 py-4 rounded-xl transition-colors">
            Log In
        </a>
    </div>
</section>

<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8 mb-20">
    <div class="bg-atp-card border border-atp-border rounded-2xl p-6 hover:border-atp-green/40 transition-colors">
        <div class="text-4xl mb-4">🗓️</div>
        <h3 class="font-display text-2xl text-white tracking-wide mb-2">Full Season Calendar</h3>
        <p class="text-gray-400 text-sm leading-relaxed">Browse all ATP tournaments — Grand Slams, Masters 1000s, 500s, and 250s — with dates, deadlines, and slots in one clean view.</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-6 hover:border-atp-green/40 transition-colors">
        <div class="text-4xl mb-4">📋</div>
        <h3 class="font-display text-2xl text-white tracking-wide mb-2">One-Click Registration</h3>
        <p class="text-gray-400 text-sm leading-relaxed">See which tournaments are open and register directly. Know instantly how many slots remain before a tournament closes.</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-6 hover:border-atp-green/40 transition-colors">
        <div class="text-4xl mb-4">📈</div>
        <h3 class="font-display text-2xl text-white tracking-wide mb-2">Ranking Points Tracker</h3>
        <p class="text-gray-400 text-sm leading-relaxed">Every tournament shows the available ranking points. Pick the tournaments that matter most for your career.</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-6 hover:border-atp-green/40 transition-colors">
        <div class="text-4xl mb-4">🌍</div>
        <h3 class="font-display text-2xl text-white tracking-wide mb-2">Surface & Location Info</h3>
        <p class="text-gray-400 text-sm leading-relaxed">Filter by surface (Hard, Clay, Grass) and see exact tournament locations. Plan your travel alongside your schedule.</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-6 hover:border-atp-green/40 transition-colors">
        <div class="text-4xl mb-4">🏆</div>
        <h3 class="font-display text-2xl text-white tracking-wide mb-2">Live Rankings Board</h3>
        <p class="text-gray-400 text-sm leading-relaxed">See where you stand on the full ATP rankings board. Track the competition and measure your progress through the season.</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-6 hover:border-atp-green/40 transition-colors">
        <div class="text-4xl mb-4">👤</div>
        <h3 class="font-display text-2xl text-white tracking-wide mb-2">Personal Dashboard</h3>
        <p class="text-gray-400 text-sm leading-relaxed">Your own dashboard shows upcoming personal tournaments, recent registrations, and a quick summary of your season at a glance.</p>
    </div>
</section>

<section class="text-center bg-atp-green/10 border border-atp-green/20 rounded-2xl py-14 px-6 mb-10">
    <h2 class="font-display text-5xl text-white tracking-wider mb-3">READY TO TAKE CONTROL?</h2>
    <p class="text-gray-400 mb-8">Create your free account and manage your entire ATP season in minutes.</p>
    <a href="<?= BASE_PATH ?>/signup.php"
       class="bg-atp-green hover:bg-green-600 text-white font-semibold text-lg px-12 py-4 rounded-xl transition-all duration-200 hover:scale-105 inline-block">
        Create Free Account
    </a>
</section>

<?php require_once 'includes/footer.php'; ?>
