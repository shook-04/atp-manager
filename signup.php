<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('/pages/dashboard.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $country  = trim($_POST['country'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($fullName))         $errors[] = "Full name is required.";
    if (empty($email))            $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (strlen($password) < 8)    $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirm)   $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "That email is already registered. Please log in instead.";
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, country) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullName, $email, $hashedPassword, $country]);

        setFlash('success', 'Account created! Please log in.');
        redirect('/login.php');
    }
}

$pageTitle = 'Sign Up';
require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="text-center mb-8">
        <h1 class="font-display text-5xl text-white tracking-wider mb-2">JOIN ATP MANAGER</h1>
        <p class="text-gray-400">Create your free player account</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-900/40 border border-red-700 rounded-xl p-4 mb-6">
        <p class="text-red-400 font-medium text-sm mb-2">Please fix the following:</p>
        <ul class="list-disc list-inside text-red-300 text-sm space-y-1">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_PATH ?>/signup.php" class="bg-atp-card border border-atp-border rounded-2xl p-8 space-y-5">

        <div>
            <label for="full_name" class="block text-sm font-medium text-gray-300 mb-1.5">Full Name</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                   placeholder="e.g. Carlos Alcaraz" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="you@example.com" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>

        <div>
            <label for="country" class="block text-sm font-medium text-gray-300 mb-1.5">Country <span class="text-gray-500">(optional)</span></label>
            <input type="text" id="country" name="country"
                   value="<?= htmlspecialchars($_POST['country'] ?? '') ?>"
                   placeholder="e.g. Spain"
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="At least 8 characters" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1.5">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Type your password again" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>

        <button type="submit"
                class="w-full bg-atp-green hover:bg-green-600 text-white font-semibold py-3.5 rounded-xl transition-colors text-base mt-2">
            Create My Account
        </button>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">
        Already have an account?
        <a href="<?= BASE_PATH ?>/login.php" class="text-atp-green hover:underline font-medium">Log in here</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
