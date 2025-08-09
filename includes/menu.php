<?php ?>
<nav class="main-menu">
    <ul>
        <li><a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a></li>
        <li><a href="<?= BASE_URL ?>/user/fartoy_nat.php">Søk i nasjons fartøy</a></li>
        <li><a href="<?= BASE_URL ?>/user/fartoy_test.php">Test søk i fartøy</a></li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li><a href="#">Administrer Data</a></li>
            <li><a href="#">Brukeradministrasjon</a></li>
        <?php endif; ?>
        <li><a href="<?= BASE_URL ?>/logout.php">Logg ut</a></li>
    </ul>
</nav>