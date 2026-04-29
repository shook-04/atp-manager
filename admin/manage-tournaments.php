<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();
$base   = BASE_PATH;
$errors = [];


if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
    $stmt->execute([(int) $_GET['delete']]);
    setFlash('success', 'Tournament deleted.');
    redirect('/admin/manage-tournaments.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int) ($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $surface     = $_POST['surface'] ?? '';
    $category    = $_POST['category'] ?? '';
    $rankPoints  = (int) ($_POST['ranking_points'] ?? 0);
    $totalSlots  = (int) ($_POST['total_slots'] ?? 32);
    $startDate   = $_POST['start_date'] ?? '';
    $endDate     = $_POST['end_date'] ?? '';
    $regDeadline = $_POST['registration_deadline'] ?? '';
    $status      = $_POST['status'] ?? 'Upcoming';

    if (empty($name))        $errors[] = "Tournament name is required.";
    if (empty($location))    $errors[] = "Location is required.";
    if (empty($startDate))   $errors[] = "Start date is required.";
    if (empty($endDate))     $errors[] = "End date is required.";
    if (empty($regDeadline)) $errors[] = "Registration deadline is required.";
    if ($rankPoints <= 0)    $errors[] = "Ranking points must be a positive number.";

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $pdo->prepare("
                UPDATE tournaments SET name=?, location=?, surface=?, category=?, ranking_points=?,
                total_slots=?, start_date=?, end_date=?, registration_deadline=?, status=? WHERE id=?
            ");
            $stmt->execute([$name, $location, $surface, $category, $rankPoints, $totalSlots, $startDate, $endDate, $regDeadline, $status, $id]);
            setFlash('success', 'Tournament updated.');
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO tournaments (name, location, surface, category, ranking_points, total_slots, start_date, end_date, registration_deadline, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $location, $surface, $category, $rankPoints, $totalSlots, $startDate, $endDate, $regDeadline, $status]);
            setFlash('success', 'Tournament added.');
        }
        redirect('/admin/manage-tournaments.php');
    }
}

$tournaments = $pdo->query("
    SELECT t.*, (SELECT COUNT(*) FROM registrations r WHERE r.tournament_id = t.id AND r.status = 'Confirmed') AS registered_count
    FROM tournaments t ORDER BY t.start_date ASC
")->fetchAll();


$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch();
}

$pageTitle = 'Manage Tournaments';
require_once '../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="font-display text-5xl text-white tracking-wider">MANAGE TOURNAMENTS</h1>
        <p class="text-yellow-400 text-sm mt-1">⚙ Admin Panel</p>
    </div>
    <a href="<?= $base ?>/pages/dashboard.php" class="text-sm text-gray-400 hover:text-white transition-colors">← Back to Dashboard</a>
</div>

<?php if (!empty($errors)): ?>
<div class="bg-red-900/40 border border-red-700 rounded-xl p-4 mb-6">
    <ul class="list-disc list-inside text-red-300 text-sm space-y-1">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>


<div class="bg-atp-card border border-atp-border rounded-2xl p-6 mb-10">
    <h2 class="font-display text-2xl text-white tracking-wide mb-5"><?= $editing ? 'EDIT TOURNAMENT' : 'ADD NEW TOURNAMENT' ?></h2>

    <form method="POST" action="<?= $base ?>/admin/manage-tournaments.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Tournament Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" placeholder="e.g. Roland Garros" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($editing['location'] ?? '') ?>" placeholder="City, Country" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Surface</label>
            <select name="surface" class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green">
                <?php foreach (['Hard', 'Clay', 'Grass', 'Indoor Hard'] as $s): ?>
                <option value="<?= $s ?>" <?= ($editing['surface'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Category</label>
            <select name="category" class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green">
                <?php foreach (['Grand Slam', 'ATP Masters 1000', 'ATP 500', 'ATP 250'] as $c): ?>
                <option value="<?= $c ?>" <?= ($editing['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Ranking Points (winner)</label>
            <input type="number" name="ranking_points" value="<?= $editing['ranking_points'] ?? '' ?>" placeholder="e.g. 2000" min="1" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Total Player Slots</label>
            <input type="number" name="total_slots" value="<?= $editing['total_slots'] ?? 32 ?>" min="2" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Start Date</label>
            <input type="date" name="start_date" value="<?= $editing['start_date'] ?? '' ?>" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">End Date</label>
            <input type="date" name="end_date" value="<?= $editing['end_date'] ?? '' ?>" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Registration Deadline</label>
            <input type="date" name="registration_deadline" value="<?= $editing['registration_deadline'] ?? '' ?>" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
            <select name="status" class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green">
                <?php foreach (['Upcoming', 'Open', 'Closed', 'Ongoing', 'Completed'] as $s): ?>
                <option value="<?= $s ?>" <?= ($editing['status'] ?? 'Upcoming') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2 flex gap-3">
            <button type="submit" class="bg-atp-green hover:bg-green-600 text-white font-semibold px-8 py-3 rounded-xl transition-colors">
                <?= $editing ? 'Save Changes' : 'Add Tournament' ?>
            </button>
            <?php if ($editing): ?>
            <a href="<?= $base ?>/admin/manage-tournaments.php" class="border border-atp-border text-gray-400 hover:text-white px-6 py-3 rounded-xl transition-colors">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>


<h2 class="font-display text-2xl text-white tracking-wide mb-4">ALL TOURNAMENTS (<?= count($tournaments) ?>)</h2>
<div class="overflow-x-auto rounded-2xl border border-atp-border">
    <table class="w-full text-sm">
        <thead class="bg-atp-dark text-gray-500 text-xs uppercase tracking-widest">
            <tr>
                <th class="px-4 py-3 text-left">Tournament</th>
                <th class="px-4 py-3 text-left">Dates</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-center">Points</th>
                <th class="px-4 py-3 text-center">Slots</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-atp-border">
            <?php foreach ($tournaments as $t): ?>
            <tr class="bg-atp-card hover:bg-atp-border/30 transition-colors">
                <td class="px-4 py-3">
                    <p class="font-medium text-white"><?= htmlspecialchars($t['name']) ?></p>
                    <p class="text-gray-500 text-xs"><?= htmlspecialchars($t['location']) ?> · <?= $t['surface'] ?></p>
                </td>
                <td class="px-4 py-3 text-gray-400"><?= date('M j', strtotime($t['start_date'])) ?> – <?= date('M j', strtotime($t['end_date'])) ?></td>
                <td class="px-4 py-3 text-gray-300 text-xs"><?= $t['category'] ?></td>
                <td class="px-4 py-3 text-center text-atp-green font-semibold"><?= number_format($t['ranking_points']) ?></td>
                <td class="px-4 py-3 text-center text-gray-400"><?= $t['registered_count'] ?>/<?= $t['total_slots'] ?></td>
                <td class="px-4 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $t['status'] === 'Open' ? 'bg-green-900/40 text-green-400' : 'bg-gray-800 text-gray-400' ?>">
                        <?= $t['status'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex justify-center gap-3">
                        <a href="<?= $base ?>/admin/manage-tournaments.php?edit=<?= $t['id'] ?>" class="text-xs text-blue-400 hover:underline">Edit</a>
                        <a href="<?= $base ?>/admin/manage-tournaments.php?delete=<?= $t['id'] ?>"
                           onclick="return confirm('Delete this tournament? All player registrations for it will also be removed.')"
                           class="text-xs text-red-400 hover:underline">Delete</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
