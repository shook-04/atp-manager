<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
$user = getCurrentUser();
$base = BASE_PATH;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tournament_id'])) {
    $tournamentId = (int) $_POST['tournament_id'];

    $stmt = $pdo->prepare("
        SELECT *, total_slots - (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.status = 'Confirmed') AS slots_left
        FROM tournaments t WHERE id = ?
    ");
    $stmt->execute([$tournamentId]);
    $tournament = $stmt->fetch();

    if (!$tournament) {
        setFlash('error', 'Tournament not found.');
    } elseif ($tournament['status'] !== 'Open') {
        setFlash('error', 'This tournament is not open for registration.');
    } elseif ($tournament['slots_left'] <= 0) {
        setFlash('error', 'Sorry, this tournament is full.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND tournament_id = ?");
        $stmt->execute([$user['id'], $tournamentId]);
        if ($stmt->fetch()) {
            setFlash('error', 'You are already registered for this tournament.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO registrations (user_id, tournament_id) VALUES (?, ?)");
            $stmt->execute([$user['id'], $tournamentId]);
            setFlash('success', 'Successfully registered for ' . $tournament['name'] . '!');
        }
    }
    redirect('/pages/tournaments.php');
}

if (isset($_GET['withdraw'])) {
    $tournamentId = (int) $_GET['withdraw'];
    $stmt = $pdo->prepare("UPDATE registrations SET status = 'Withdrawn' WHERE user_id = ? AND tournament_id = ?");
    $stmt->execute([$user['id'], $tournamentId]);
    setFlash('info', 'You have withdrawn from the tournament.');
    redirect('/pages/tournaments.php');
}

$filterStatus   = $_GET['status']   ?? '';
$filterSurface  = $_GET['surface']  ?? '';
$filterCategory = $_GET['category'] ?? '';

$where  = [];
$params = [$user['id']]; 

if ($filterStatus)   { $where[] = "t.status = ?";   $params[] = $filterStatus; }
if ($filterSurface)  { $where[] = "t.surface = ?";  $params[] = $filterSurface; }
if ($filterCategory) { $where[] = "t.category = ?"; $params[] = $filterCategory; }

$sql = "
    SELECT t.*,
           (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.status = 'Confirmed') AS registered_count,
           t.total_slots - (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.status = 'Confirmed') AS slots_left,
           (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.user_id = ?) AS is_registered
    FROM tournaments t
";

if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY t.start_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tournaments = $stmt->fetchAll();

function categoryColor($c) {
    return match($c) {
        'Grand Slam'       => 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30',
        'ATP Masters 1000' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
        'ATP 500'          => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
        default            => 'bg-gray-700 text-gray-300 border-gray-600',
    };
}
function statusColor($s) {
    return match($s) {
        'Open'      => 'bg-green-500/20 text-green-300 border-green-500/30',
        'Upcoming'  => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
        'Closed'    => 'bg-red-500/20 text-red-300 border-red-500/30',
        'Ongoing'   => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
        default     => 'bg-gray-700 text-gray-400 border-gray-600',
    };
}

$pageTitle = 'Tournaments';
require_once '../includes/header.php';
?>

<div class="mb-6">
    <h1 class="font-display text-5xl text-white tracking-wider">2025 ATP SEASON</h1>
    <p class="text-gray-400 mt-1">Browse tournaments and register for available events.</p>
</div>

<!-- Filters -->
<form method="GET" action="<?= $base ?>/pages/tournaments.php" class="flex flex-wrap gap-3 mb-8">
    <select name="status" class="bg-atp-card border border-atp-border text-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-atp-green">
        <option value="">All Statuses</option>
        <?php foreach (['Open', 'Upcoming', 'Ongoing', 'Closed', 'Completed'] as $s): ?>
        <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
    </select>
    <select name="surface" class="bg-atp-card border border-atp-border text-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-atp-green">
        <option value="">All Surfaces</option>
        <?php foreach (['Hard', 'Clay', 'Grass', 'Indoor Hard'] as $s): ?>
        <option value="<?= $s ?>" <?= $filterSurface === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
    </select>
    <select name="category" class="bg-atp-card border border-atp-border text-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-atp-green">
        <option value="">All Categories</option>
        <?php foreach (['Grand Slam', 'ATP Masters 1000', 'ATP 500', 'ATP 250'] as $c): ?>
        <option value="<?= $c ?>" <?= $filterCategory === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="bg-atp-green hover:bg-green-600 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">Filter</button>
    <?php if ($filterStatus || $filterSurface || $filterCategory): ?>
    <a href="<?= $base ?>/pages/tournaments.php" class="border border-atp-border text-gray-400 hover:text-white px-5 py-2 rounded-lg text-sm transition-colors">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($tournaments)): ?>
<div class="text-center py-20 text-gray-500">
    <p class="text-5xl mb-4">🔍</p>
    <p class="font-medium text-lg">No tournaments found matching your filters.</p>
    <a href="<?= $base ?>/pages/tournaments.php" class="text-atp-green hover:underline text-sm mt-2 inline-block">Clear filters</a>
</div>

<?php else: ?>
<div class="space-y-3">
    <?php foreach ($tournaments as $t): ?>
    <div class="bg-atp-card border border-atp-border rounded-2xl p-5 <?= $t['is_registered'] ? 'border-l-4 border-l-atp-green' : '' ?> hover:border-gray-600 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">

            <!-- Info -->
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h3 class="font-semibold text-white"><?= htmlspecialchars($t['name']) ?></h3>
                    <span class="text-xs px-2 py-0.5 rounded-full border font-medium <?= categoryColor($t['category']) ?>"><?= $t['category'] ?></span>
                    <span class="text-xs px-2 py-0.5 rounded-full border font-medium <?= statusColor($t['status']) ?>"><?= $t['status'] ?></span>
                    <?php if ($t['is_registered']): ?>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-atp-green/20 text-atp-green border border-atp-green/30 font-medium">✓ Registered</span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-400">
                    <span>📍 <?= htmlspecialchars($t['location']) ?></span>
                    <span>🎾 <?= $t['surface'] ?></span>
                    <span>📅 <?= date('M j', strtotime($t['start_date'])) ?> – <?= date('M j, Y', strtotime($t['end_date'])) ?></span>
                    <?php if ($t['status'] === 'Open'): ?>
                    <span class="text-yellow-400">⏰ Deadline: <?= date('M j', strtotime($t['registration_deadline'])) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex gap-6 text-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Points</p>
                    <p class="font-display text-2xl text-atp-green"><?= number_format($t['ranking_points']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Slots Left</p>
                    <p class="font-display text-2xl <?= $t['slots_left'] <= 5 ? 'text-red-400' : 'text-white' ?>"><?= $t['slots_left'] ?></p>
                </div>
            </div>

            <div class="sm:w-32 flex-shrink-0">
                <?php if ($t['is_registered'] && $t['status'] !== 'Completed'): ?>
                    <a href="<?= $base ?>/pages/tournaments.php?withdraw=<?= $t['id'] ?>"
                       onclick="return confirm('Are you sure you want to withdraw?')"
                       class="block w-full text-center text-sm border border-red-700/50 text-red-400 hover:bg-red-900/30 px-4 py-2 rounded-xl transition-colors">
                        Withdraw
                    </a>
                <?php elseif ($t['status'] === 'Open' && $t['slots_left'] > 0 && !$t['is_registered']): ?>
                    <form method="POST" action="<?= $base ?>/pages/tournaments.php">
                        <input type="hidden" name="tournament_id" value="<?= $t['id'] ?>">
                        <button type="submit" class="w-full bg-atp-green hover:bg-green-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
                            Register
                        </button>
                    </form>
                <?php elseif ($t['slots_left'] <= 0): ?>
                    <span class="block w-full text-center text-xs text-red-400 border border-red-900/40 px-4 py-2 rounded-xl">Full</span>
                <?php elseif ($t['status'] === 'Upcoming'): ?>
                    <span class="block w-full text-center text-xs text-gray-500 border border-atp-border px-4 py-2 rounded-xl">Not Open Yet</span>
                <?php else: ?>
                    <span class="block w-full text-center text-xs text-gray-500 border border-atp-border px-4 py-2 rounded-xl"><?= $t['status'] ?></span>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
</div>
<p class="text-gray-500 text-sm mt-4"><?= count($tournaments) ?> tournament<?= count($tournaments) !== 1 ? 's' : '' ?> shown.</p>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
