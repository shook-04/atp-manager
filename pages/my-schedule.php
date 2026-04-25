<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
$user = getCurrentUser();
$base = BASE_PATH;


$stmt = $pdo->prepare("
    SELECT t.*, r.status AS reg_status, r.registered_at
    FROM registrations r
    JOIN tournaments t ON r.tournament_id = t.id
    WHERE r.user_id = ?
    ORDER BY t.start_date ASC
");
$stmt->execute([$user['id']]);
$allRegistrations = $stmt->fetchAll();

$upcoming = [];
$past     = [];
$today    = date('Y-m-d');

foreach ($allRegistrations as $reg) {
    if ($reg['end_date'] >= $today) $upcoming[] = $reg;
    else $past[] = $reg;
}

$stmt = $pdo->prepare("
    SELECT SUM(t.ranking_points) as total_points
    FROM registrations r
    JOIN tournaments t ON r.tournament_id = t.id
    WHERE r.user_id = ? AND r.status = 'Confirmed'
");
$stmt->execute([$user['id']]);
$totalPoints = $stmt->fetch()['total_points'] ?? 0;

$pageTitle = 'My Schedule';
require_once '../includes/header.php';
?>

<div class="mb-8">
    <h1 class="font-display text-5xl text-white tracking-wider">MY SCHEDULE</h1>
    <p class="text-gray-400 mt-1">Your personal tournament calendar for the 2025 ATP season.</p>
</div>

<div class="grid grid-cols-3 gap-4 mb-10">
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5 text-center">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Upcoming</p>
        <p class="font-display text-5xl text-atp-green"><?= count($upcoming) ?></p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5 text-center">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Completed</p>
        <p class="font-display text-5xl text-white"><?= count($past) ?></p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5 text-center">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Points Available</p>
        <p class="font-display text-5xl text-yellow-400"><?= number_format($totalPoints) ?></p>
    </div>
</div>

<h2 class="font-display text-3xl text-white tracking-wide mb-4">UPCOMING TOURNAMENTS</h2>

<?php if (empty($upcoming)): ?>
<div class="bg-atp-card border border-atp-border rounded-2xl p-10 text-center mb-10">
    <p class="text-5xl mb-3">📅</p>
    <p class="text-gray-400 font-medium">No upcoming tournaments scheduled.</p>
    <a href="<?= $base ?>/pages/tournaments.php" class="text-atp-green hover:underline text-sm mt-2 inline-block">Browse open tournaments →</a>
</div>
<?php else: ?>
<div class="space-y-3 mb-10">
    <?php foreach ($upcoming as $reg): ?>
    <div class="bg-atp-card border border-atp-green/30 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="text-center bg-atp-dark border border-atp-border rounded-xl px-4 py-3 min-w-[80px]">
            <p class="text-gray-400 text-xs uppercase"><?= date('M', strtotime($reg['start_date'])) ?></p>
            <p class="font-display text-3xl text-white"><?= date('j', strtotime($reg['start_date'])) ?></p>
        </div>
        <div class="flex-1">
            <h3 class="font-semibold text-white"><?= htmlspecialchars($reg['name']) ?></h3>
            <p class="text-gray-400 text-sm"><?= htmlspecialchars($reg['location']) ?> · <?= $reg['surface'] ?> · <?= $reg['category'] ?></p>
            <p class="text-gray-500 text-xs mt-1">Ends: <?= date('M j, Y', strtotime($reg['end_date'])) ?> | Registered: <?= date('M j', strtotime($reg['registered_at'])) ?></p>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Points</p>
                <p class="font-display text-2xl text-atp-green"><?= number_format($reg['ranking_points']) ?></p>
            </div>
            <?php if ($reg['reg_status'] === 'Confirmed'): ?>
            <span class="text-xs bg-atp-green/20 text-atp-green border border-atp-green/30 px-3 py-1 rounded-full font-medium">✓ Confirmed</span>
            <?php elseif ($reg['reg_status'] === 'Withdrawn'): ?>
            <span class="text-xs bg-red-900/30 text-red-400 border border-red-700/30 px-3 py-1 rounded-full font-medium">Withdrawn</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($past)): ?>
<h2 class="font-display text-3xl text-white tracking-wide mb-4">PAST TOURNAMENTS</h2>
<div class="space-y-2">
    <?php foreach (array_reverse($past) as $reg): ?>
    <div class="bg-atp-card border border-atp-border rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-3 opacity-70">
        <div class="flex-1">
            <p class="font-medium text-white text-sm"><?= htmlspecialchars($reg['name']) ?></p>
            <p class="text-gray-500 text-xs"><?= htmlspecialchars($reg['location']) ?> · <?= date('M j, Y', strtotime($reg['start_date'])) ?></p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-xs text-gray-400"><?= $reg['category'] ?></span>
            <span class="text-xs text-atp-green font-medium"><?= number_format($reg['ranking_points']) ?> pts</span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
