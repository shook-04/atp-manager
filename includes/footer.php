<?php
$base = defined('BASE_PATH') ? BASE_PATH : '';
?>
</main>

<footer class="border-t border-atp-border mt-16 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
        <span class="font-display text-2xl text-atp-green tracking-wider">ATP Manager</span>
        <p class="text-sm text-gray-500">&copy; <?= date('Y') ?> ATP Manager &mdash; Tournament Scheduling for Players</p>
        <div class="flex gap-4 text-sm text-gray-500">
            <a href="<?= $base ?>/pages/tournaments.php" class="hover:text-white transition-colors">Tournaments</a>
            <a href="<?= $base ?>/pages/rankings.php" class="hover:text-white transition-colors">Rankings</a>
        </div>
    </div>
</footer>

</body>
</html>
