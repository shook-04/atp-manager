<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
$user = getCurrentUser();
$base = BASE_PATH;

$rankedPlayers = $pdo->query("
    SELECT id, full_name, country, ranking
    FROM users
    WHERE role = 'player'
    ORDER BY ranking ASC
")->fetchAll();

$ranked   = array_filter($rankedPlayers, fn($p) => $p['ranking'] !== null);
$unranked = array_filter($rankedPlayers, fn($p) => $p['ranking'] === null);

$pageTitle = 'ATP Rankings';
require_once '../includes/header.php';
?>

<div class="mb-8">
    <h1 class="font-display text-5xl text-white tracking-wider">ATP RANKINGS</h1>
    <p class="text-gray-400 mt-1">Current world rankings for players registered in ATP Manager.</p>
</div>

<?php
$myRanking = null;
foreach ($ranked as $p) { if ($p['id'] == $user['id']) { $myRanking = $p; break; } }
?>

<?php if ($myRanking): ?>
<div class="bg-atp-green/10 border border-atp-green/30 rounded-2xl p-5 mb-8 flex items-center gap-5">
    <div class="font-display text-6xl text-atp-green">#<?= $myRanking['ranking'] ?></div>
    <div>
        <p class="text-white font-semibold"><?= htmlspecialchars($myRanking['full_name']) ?> <span class="text-atp-green text-sm">(You)</span></p>
        <p class="text-gray-400 text-sm"><?= htmlspecialchars($myRanking['country'] ?: 'Country unknown') ?></p>
    </div>
</div>
<?php else: ?>
<div class="bg-atp-card border border-atp-border rounded-2xl p-5 mb-8">
    <p class="text-gray-400">You are not currently ranked. Rankings are assigned by the admin.</p>
</div>
<?php endif; ?>

<div class="bg-atp-card border border-atp-border rounded-2xl overflow-hidden">
    <div class="grid grid-cols-12 px-6 py-3 bg-atp-dark border-b border-atp-border text-xs uppercase tracking-widest text-gray-500 font-medium">
        <div class="col-span-2">Rank</div>
        <div class="col-span-6">Player</div>
        <div class="col-span-4">Country</div>
    </div>

    <?php foreach ($ranked as $player): ?>
    <div class="grid grid-cols-12 px-6 py-4 border-b border-atp-border items-center <?= $player['id'] == $user['id'] ? 'bg-atp-green/5' : 'hover:bg-atp-border/30' ?> transition-colors">
        <div class="col-span-2 font-display text-3xl <?= $player['ranking'] === 1 ? 'text-yellow-400' : ($player['ranking'] === 2 ? 'text-gray-300' : ($player['ranking'] === 3 ? 'text-amber-600' : 'text-gray-500')) ?>">
            <?= $player['ranking'] === 1 ? '🥇' : ($player['ranking'] === 2 ? '🥈' : ($player['ranking'] === 3 ? '🥉' : '#' . $player['ranking'])) ?>
        </div>
        <div class="col-span-6 flex items-center gap-2">
            <span class="font-semibold text-white"><?= htmlspecialchars($player['full_name']) ?></span>
            <?php if ($player['id'] == $user['id']): ?>
            <span class="text-xs bg-atp-green/20 text-atp-green border border-atp-green/30 px-2 py-0.5 rounded-full">You</span>
            <?php endif; ?>
        </div>
        <div class="col-span-4 text-gray-400 text-sm"><?= htmlspecialchars($player['country'] ?: '—') ?></div>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($unranked)): ?>
    <div class="px-6 py-3 bg-atp-dark border-b border-atp-border text-xs uppercase tracking-widest text-gray-600 font-medium">Unranked Players</div>
    <?php foreach ($unranked as $player): ?>
    <div class="grid grid-cols-12 px-6 py-4 border-b border-atp-border items-center opacity-50 last:border-b-0">
        <div class="col-span-2 text-gray-600 text-sm">—</div>
        <div class="col-span-6 text-gray-400"><?= htmlspecialchars($player['full_name']) ?></div>
        <div class="col-span-4 text-gray-600 text-sm"><?= htmlspecialchars($player['country'] ?: '—') ?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<p class="text-gray-600 text-xs mt-4">Total players: <?= count($rankedPlayers) ?></p>

<?php require_once '../includes/footer.php'; ?>
