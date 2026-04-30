<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
$user = getCurrentUser();
$base = BASE_PATH;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();


if ($user['role'] === 'admin') {

    $totalTournaments   = $pdo->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();
    $totalPlayers       = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'player'")->fetchColumn();
    $totalRegistrations = $pdo->query("SELECT COUNT(*) FROM registrations WHERE status = 'Confirmed'")->fetchColumn();
    $totalWithdrawals   = $pdo->query("SELECT COUNT(*) FROM registrations WHERE status = 'Withdrawn'")->fetchColumn();

    $statusCounts = $pdo->query("SELECT status, COUNT(*) as count FROM tournaments GROUP BY status")->fetchAll();
    $byStatus = [];
    foreach ($statusCounts as $row) { $byStatus[$row['status']] = $row['count']; }

    $allTournaments = $pdo->query("
        SELECT t.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.status = 'Confirmed') AS registered_count
        FROM tournaments t ORDER BY t.start_date ASC
    ")->fetchAll();

    $recentRegistrations = $pdo->query("
        SELECT u.full_name, u.country, t.name AS tournament_name, r.registered_at, r.status
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        JOIN tournaments t ON r.tournament_id = t.id
        ORDER BY r.registered_at DESC LIMIT 5
    ")->fetchAll();


} else {

    $stmt = $pdo->prepare("
        SELECT t.name, t.location, t.start_date, t.end_date,
               t.surface, t.category, t.ranking_points, r.status
        FROM registrations r
        JOIN tournaments t ON r.tournament_id = t.id
        WHERE r.user_id = ? AND r.status = 'Confirmed'
          AND t.end_date >= CURDATE()
        ORDER BY t.start_date ASC LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $upcomingRegistrations = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registrations WHERE user_id = ? AND status = 'Confirmed'");
    $stmt->execute([$user['id']]);
    $totalRegistrations = $stmt->fetch()['total'];

    $openTournaments = $pdo->query("
        SELECT t.*,
               t.total_slots - (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.status = 'Confirmed') AS slots_left
        FROM tournaments t WHERE t.status = 'Open'
        ORDER BY t.start_date ASC LIMIT 3
    ")->fetchAll();
}

$pageTitle = 'Dashboard';
require_once '../includes/header.php';
?>

<?php if ($user['role'] === 'admin'): ?>


<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="font-display text-4xl sm:text-5xl text-white tracking-wider">
            ADMIN <span class="text-yellow-400">DASHBOARD</span>
        </h1>
        <p class="text-gray-400 mt-1">System overview and tournament management.</p>
    </div>
    <a href="<?= $base ?>/admin/manage-tournaments.php"
       class="inline-flex items-center gap-2 bg-yellow-500/20 hover:bg-yellow-500/30 text-yellow-300 border border-yellow-500/30 px-5 py-2.5 rounded-xl transition-colors font-medium text-sm">
        ⚙ Manage Tournaments
    </a>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Tournaments</p>
        <p class="font-display text-5xl text-white"><?= $totalTournaments ?></p>
        <p class="text-gray-500 text-xs mt-1">In the system</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Players</p>
        <p class="font-display text-5xl text-atp-green"><?= $totalPlayers ?></p>
        <p class="text-gray-500 text-xs mt-1">Registered accounts</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Registrations</p>
        <p class="font-display text-5xl text-white"><?= $totalRegistrations ?></p>
        <p class="text-gray-500 text-xs mt-1">Confirmed entries</p>
    </div>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">Withdrawals</p>
        <p class="font-display text-5xl text-red-400"><?= $totalWithdrawals ?></p>
        <p class="text-gray-500 text-xs mt-1">Players withdrawn</p>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

    <div class="lg:col-span-1">
        <h2 class="font-display text-2xl text-white tracking-wide mb-4">TOURNAMENT STATUS</h2>
        <div class="bg-atp-card border border-atp-border rounded-2xl p-5 space-y-4">
            <?php
            $statusConfig = [
                'Open'      => ['color' => 'text-green-400',  'bar' => 'bg-green-400'],
                'Upcoming'  => ['color' => 'text-blue-400',   'bar' => 'bg-blue-400'],
                'Ongoing'   => ['color' => 'text-orange-400', 'bar' => 'bg-orange-400'],
                'Closed'    => ['color' => 'text-red-400',    'bar' => 'bg-red-400'],
                'Completed' => ['color' => 'text-gray-400',   'bar' => 'bg-gray-400'],
            ];
            foreach ($statusConfig as $status => $config):
                $count = $byStatus[$status] ?? 0;
                $pct   = $totalTournaments > 0 ? round(($count / $totalTournaments) * 100) : 0;
            ?>
            <div>
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-sm <?= $config['color'] ?> font-medium"><?= $status ?></span>
                    <span class="font-display text-xl text-white"><?= $count ?></span>
                </div>
                <div class="w-full bg-atp-border rounded-full h-1.5">
                    <div class="<?= $config['bar'] ?> h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    
    <div class="lg:col-span-2">
        <h2 class="font-display text-2xl text-white tracking-wide mb-4">RECENT REGISTRATIONS</h2>
        <?php if (empty($recentRegistrations)): ?>
        <div class="bg-atp-card border border-atp-border rounded-2xl p-8 text-center">
            <p class="text-gray-500">No registrations yet.</p>
        </div>
        <?php else: ?>
        <div class="bg-atp-card border border-atp-border rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-atp-dark text-gray-500 text-xs uppercase tracking-widest">
                    <tr>
                        <th class="px-4 py-3 text-left">Player</th>
                        <th class="px-4 py-3 text-left">Tournament</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-atp-border">
                    <?php foreach ($recentRegistrations as $reg): ?>
                    <tr class="hover:bg-atp-border/30 transition-colors">
                        <td class="px-4 py-3">
                            <p class="text-white font-medium"><?= htmlspecialchars($reg['full_name']) ?></p>
                            <p class="text-gray-500 text-xs"><?= htmlspecialchars($reg['country'] ?: '—') ?></p>
                        </td>
                        <td class="px-4 py-3 text-gray-300 text-xs"><?= htmlspecialchars($reg['tournament_name']) ?></td>
                        <td class="px-4 py-3 text-gray-400 text-xs"><?= date('M j, Y', strtotime($reg['registered_at'])) ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                <?= $reg['status'] === 'Confirmed' ? 'bg-green-900/40 text-green-400' : 'bg-red-900/40 text-red-400' ?>">
                                <?= $reg['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>


<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-display text-2xl text-white tracking-wide">ALL TOURNAMENTS</h2>
        <a href="<?= $base ?>/admin/manage-tournaments.php" class="text-atp-green text-sm hover:underline">Manage →</a>
    </div>
    <div class="overflow-x-auto rounded-2xl border border-atp-border">
        <table class="w-full text-sm">
            <thead class="bg-atp-dark text-gray-500 text-xs uppercase tracking-widest">
                <tr>
                    <th class="px-4 py-3 text-left">Tournament</th>
                    <th class="px-4 py-3 text-left">Dates</th>
                    <th class="px-4 py-3 text-center">Points</th>
                    <th class="px-4 py-3 text-center">Entries</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-atp-border">
                <?php foreach ($allTournaments as $t):
                    $fillPct = $t['total_slots'] > 0 ? round(($t['registered_count'] / $t['total_slots']) * 100) : 0;
                ?>
                <tr class="bg-atp-card hover:bg-atp-border/30 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-white"><?= htmlspecialchars($t['name']) ?></p>
                        <p class="text-gray-500 text-xs"><?= htmlspecialchars($t['location']) ?> · <?= $t['surface'] ?></p>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">
                        <?= date('M j', strtotime($t['start_date'])) ?> – <?= date('M j', strtotime($t['end_date'])) ?>
                    </td>
                    <td class="px-4 py-3 text-center text-atp-green font-semibold"><?= number_format($t['ranking_points']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center gap-2 justify-center">
                            <span class="text-gray-300 text-xs"><?= $t['registered_count'] ?>/<?= $t['total_slots'] ?></span>
                            <div class="w-16 bg-atp-border rounded-full h-1.5">
                                <div class="bg-atp-green h-1.5 rounded-full" style="width: <?= $fillPct ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            <?= $t['status'] === 'Open'      ? 'bg-green-900/40 text-green-400'   : '' ?>
                            <?= $t['status'] === 'Upcoming'  ? 'bg-blue-900/40 text-blue-400'     : '' ?>
                            <?= $t['status'] === 'Completed' ? 'bg-gray-800 text-gray-400'         : '' ?>
                            <?= $t['status'] === 'Closed'    ? 'bg-red-900/40 text-red-400'       : '' ?>
                            <?= $t['status'] === 'Ongoing'   ? 'bg-orange-900/40 text-orange-400' : '' ?>">
                            <?= $t['status'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>


<div class="mb-8">
    <h1 class="font-display text-4xl sm:text-5xl text-white tracking-wider">
        WELCOME, <span class="text-atp-green"><?= strtoupper(explode(' ', $profile['full_name'])[0]) ?></span>
    </h1>
    <p class="text-gray-400 mt-1">Here's your season overview.</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5">
        <p class="text-gray-400 text-xs uppercase tracking-widest mb-1">ATP Ranking</p>
        <p class="font-display text-5xl text-atp-green"><?= $profile['ranking'] ? '#' . $profile['ranking'] : '—' ?></p>
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
            <p class="text-gray-400 font-medium">No tournaments registered yet.</p>
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
            <p class="text-gray-500 text-xs mt-1">Ask the admin to open a tournament.</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($openTournaments as $t): ?>
            <div class="bg-atp-card border border-atp-green/30 rounded-xl p-4 flex justify-between items-start">
                <div>
                    <p class="font-semibold text-white text-sm"><?= htmlspecialchars($t['name']) ?></p>
                    <p class="text-gray-400 text-xs mt-0.5"><?= htmlspecialchars($t['location']) ?> · <?= $t['surface'] ?></p>
                    <p class="text-gray-500 text-xs mt-1"><?= date('M j', strtotime($t['start_date'])) ?> – <?= date('M j, Y', strtotime($t['end_date'])) ?></p>
                </div>
                <div class="text-right flex flex-col items-end gap-2">
                    <span class="text-xs font-semibold text-atp-green"><?= number_format($t['ranking_points']) ?> pts</span>
                    <span class="text-xs text-gray-400"><?= $t['slots_left'] ?> slots left</span>
                    <a href="<?= $base ?>/pages/tournaments.php" class="text-xs bg-atp-green hover:bg-green-600 text-white px-3 py-1 rounded-lg transition-colors">Register</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
