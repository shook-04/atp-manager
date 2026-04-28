<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();
$user = getCurrentUser();
$base = BASE_PATH;
$errors = [];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $country  = trim($_POST['country'] ?? '');

        if (empty($fullName)) {
            $errors[] = "Full name cannot be empty.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, country = ? WHERE id = ?");
            $stmt->execute([$fullName, $country, $user['id']]);
            $_SESSION['user_name'] = $fullName;
            setFlash('success', 'Profile updated successfully.');
            redirect('/pages/profile.php');
        }
    }

    if ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $profile['password'])) {
            $errors[] = "Your current password is incorrect.";
        } elseif (strlen($newPassword) < 8) {
            $errors[] = "New password must be at least 8 characters.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match.";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            setFlash('success', 'Password changed successfully.');
            redirect('/pages/profile.php');
        }
    }
}

$pageTitle = 'My Profile';
require_once '../includes/header.php';
?>

<div class="mb-8">
    <h1 class="font-display text-5xl text-white tracking-wider">MY PROFILE</h1>
    <p class="text-gray-400 mt-1">View and update your account details.</p>
</div>

<?php if (!empty($errors)): ?>
<div class="bg-red-900/40 border border-red-700 rounded-xl p-4 mb-6">
    <ul class="list-disc list-inside text-red-300 text-sm space-y-1">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-1">
        <div class="bg-atp-card border border-atp-border rounded-2xl p-6 text-center">
            <div class="w-20 h-20 rounded-full bg-atp-green/20 border-2 border-atp-green/50 flex items-center justify-center mx-auto mb-4">
                <span class="font-display text-3xl text-atp-green"><?= strtoupper(substr($profile['full_name'], 0, 1)) ?></span>
            </div>
            <h2 class="font-semibold text-white text-lg"><?= htmlspecialchars($profile['full_name']) ?></h2>
            <p class="text-gray-400 text-sm mt-1"><?= htmlspecialchars($profile['email']) ?></p>
            <?php if ($profile['country']): ?>
            <p class="text-gray-500 text-sm mt-1">🌍 <?= htmlspecialchars($profile['country']) ?></p>
            <?php endif; ?>
            <?php if ($profile['ranking']): ?>
            <div class="mt-4 bg-atp-green/10 border border-atp-green/20 rounded-xl p-3">
                <p class="text-gray-400 text-xs uppercase tracking-widest">ATP Ranking</p>
                <p class="font-display text-4xl text-atp-green">#<?= $profile['ranking'] ?></p>
            </div>
            <?php else: ?>
            <div class="mt-4 bg-atp-dark border border-atp-border rounded-xl p-3">
                <p class="text-gray-500 text-sm">Not yet ranked</p>
            </div>
            <?php endif; ?>
            <p class="text-gray-600 text-xs mt-4">Member since <?= date('F Y', strtotime($profile['created_at'])) ?></p>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">

        <div class="bg-atp-card border border-atp-border rounded-2xl p-6">
            <h3 class="font-display text-2xl text-white tracking-wide mb-5">EDIT PROFILE</h3>
            <form method="POST" action="<?= $base ?>/pages/profile.php" class="space-y-4">
                <input type="hidden" name="action" value="update_profile">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($profile['full_name']) ?>" required
                           class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Email <span class="text-gray-500">(cannot be changed)</span></label>
                    <input type="email" value="<?= htmlspecialchars($profile['email']) ?>" disabled
                           class="w-full bg-atp-dark border border-atp-border text-gray-500 rounded-lg px-4 py-3 text-sm cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Country</label>
                    <input type="text" name="country" value="<?= htmlspecialchars($profile['country'] ?? '') ?>" placeholder="e.g. Spain"
                           class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
                </div>
                <button type="submit" class="bg-atp-green hover:bg-green-600 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm">
                    Save Changes
                </button>
            </form>
        </div>

        <div class="bg-atp-card border border-atp-border rounded-2xl p-6">
            <h3 class="font-display text-2xl text-white tracking-wide mb-5">CHANGE PASSWORD</h3>
            <form method="POST" action="<?= $base ?>/pages/profile.php" class="space-y-4">
                <input type="hidden" name="action" value="change_password">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter your current password" required
                           class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">New Password</label>
                    <input type="password" name="new_password" placeholder="At least 8 characters" required
                           class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Type new password again" required
                           class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
                </div>
                <button type="submit" class="bg-red-900/50 hover:bg-red-900/70 border border-red-700/50 text-red-300 font-semibold px-6 py-2.5 rounded-xl transition-colors text-sm">
                    Change Password
                </button>
            </form>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
