<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (isLoggedIn()) redirect('/pages/dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['user_name']    = $user['full_name'];
            $_SESSION['user_role']    = $user['role'];
            $_SESSION['user_ranking'] = $user['ranking'];

            setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect('/pages/dashboard.php');
        } else {
            $error = "Incorrect email or password. Please try again.";
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto">
    <div class="text-center mb-8">
        <h1 class="font-display text-5xl text-white tracking-wider mb-2">WELCOME BACK</h1>
        <p class="text-gray-400">Sign in to your ATP Manager account</p>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-900/40 border border-red-700 rounded-xl px-4 py-3 mb-6 text-red-300 text-sm">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_PATH ?>/login.php" class="bg-atp-card border border-atp-border rounded-2xl p-8 space-y-5">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="you@example.com" required autofocus
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="Your password" required
                   class="w-full bg-atp-dark border border-atp-border text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-atp-green focus:ring-1 focus:ring-atp-green transition-colors">
        </div>

        <button type="submit"
                class="w-full bg-atp-green hover:bg-green-600 text-white font-semibold py-3.5 rounded-xl transition-colors text-base mt-2">
            Sign In
        </button>
    </form>

    <p class="text-center text-gray-500 text-sm mt-6">
        Don't have an account?
        <a href="<?= BASE_PATH ?>/signup.php" class="text-atp-green hover:underline font-medium">Create one here</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
