<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
$user = getCurrentUser();
$base = BASE_PATH;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT t.name, t.location, t.start_date, t.end_date, t.surface, t.category, t.ranking_points, r.status
    FROM registrations r
    JOIN tournaments t ON r.tournament_id = t.id
    WHERE r.user_id = ? AND t.start_date >= CURDATE()
    ORDER BY t.start_date ASC
    LIMIT 5
");
$stmt->execute([$user['id']]);
$upcomingRegistrations = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registrations WHERE user_id = ?");
$stmt->execute([$user['id']]);
$totalRegistrations = $stmt->fetch()['total'];

$openTournaments = $pdo->query("
    SELECT t.*,
           t.total_slots - (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.status = 'Confirmed') AS slots_left
    FROM tournaments t
    WHERE t.status = 'Open' AND t.registration_deadline >= CURDATE()
    ORDER BY t.start_date ASC
    LIMIT 3
")->fetchAll();

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
?>

<div class="mb-8">
    <h1 class="font-display text-4xl sm:text-5xl text-white tracking-wider">
        WELCOME, <span class="text-atp-green"><?= strtoupper(explode(' ', $profile['full_name'])[0]) ?></span>
    </h1>
    <p class="text-gray-400 mt-1">Here's your season overview.</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">ATP Ranking</p>
        <p class="font-display text-5xl text-atp-green">
            <?= $profile['ranking'] ? '#' . $profile['ranking'] : '—' ?>
        </p>
        <p class="text-gray-500 text-xs mt-1"><?= $profile['ranking'] ? 'World ranking' : 'Not yet ranked' ?></p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Country</p>
        <p class="font-display text-3xl text-white mt-2"><?= htmlspecialchars($profile['country'] ?: '—') ?></p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Registered</p>
        <p class="font-display text-5xl text-white"><?= $totalRegistrations ?></p>
        <p class="text-gray-500 text-xs mt-1">Tournaments this season</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Open Now</p>
        <p class="font-display text-5xl text-yellow-400"><?= count($openTournaments) ?></p>
        <p class="text-gray-500 text-xs mt-1">Accepting entries</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-display text-2xl text-white tracking-wide">MY UPCOMING SCHEDULE</h2>
            <a href="<?= $base ?>/pages/my-schedule.php" class="text-atp-green text-sm hover:underline">View all →</a>
        </div>

        <?php if (empty($upcomingRegistrations)): ?>
        <div class="bg-atp-card border border-atp-border rounded-2xl p-8 text-center">
            <p class="text-gray-500 text-4xl mb-3">📋</p>
            <p class="text-gray-400 font-medium">No upcoming tournaments yet.</p>
            <a href="<?= $base ?>/pages/tournaments.php" class="text-atp-green text-sm hover:underline mt-2 inline-block">Browse open tournaments →</a>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($upcomingRegistrations as $reg): ?>
            <div class="bg-atp-card border border-atp-border rounded-xl p-4 flex justify-between items-start">
                <div>
                    <p class="font-semibold text-white text-sm"><?= htmlspecialchars($reg['name']) ?></p>
                    <p class="text-gray-400 text-xs mt-0.5"><?= htmlspecialchars($reg['location']) ?></p>
                    <p class="text-gray-500 text-xs mt-1"><?= date('M j', strtotime($reg['start_date'])) ?> – <?= date('M j, Y', strtotime($reg['end_date'])) ?></p>
                </div>
                <div class="text-right">
                    <span class="inline-block text-xs px-2 py-0.5 rounded-full bg-atp-border text-gray-300 mb-1"><?= $reg['surface'] ?></span>
                    <p class="text-atp-green text-xs font-semibold"><?= number_format($reg['ranking_points']) ?> pts</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div>
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-display text-2xl text-white tracking-wide">OPEN FOR ENTRY</h2>
            <a href="<?= $base ?>/pages/tournaments.php" class="text-atp-green text-sm hover:underline">Browse all →</a>
        </div>

        <?php if (empty($openTournaments)): ?>
        <div class="bg-atp-card border border-atp-border rounded-2xl p-8 text-center">
            <p class="text-gray-500 text-4xl mb-3">🎾</p>
            <p class="text-gray-400 font-medium">No tournaments open right now.</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($openTournaments as $t): ?>
            <div class="bg-atp-card border border-atp-green/30 rounded-xl p-4 flex justify-between items-start">
                <div>
                    <p class="font-semibold text-white text-sm"><?= htmlspecialchars($t['name']) ?></p>
                    <p class="text-gray-400 text-xs mt-0.5"><?= htmlspecialchars($t['location']) ?> · <?= $t['surface'] ?></p>
                    <p class="text-gray-500 text-xs mt-1">Deadline: <?= date('M j, Y', strtotime($t['registration_deadline'])) ?></p>
                </div>
                <div class="text-right flex flex-col items-end gap-2">
                    <span class="text-xs font-semibold text-atp-green"><?= number_format($t['ranking_points']) ?> pts</span>
                    <span class="text-xs text-gray-400"><?= $t['slots_left'] ?> slots left</span>
                    <a href="<?= $base ?>/pages/tournaments.php"
                       class="text-xs bg-atp-green hover:bg-green-600 text-white px-3 py-1 rounded-lg transition-colors">
                        Register
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
