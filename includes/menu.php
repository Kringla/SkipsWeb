<?php ?>
<nav class="main-menu">
    <ul>
        <li><a href="/dashboard.php">Dashboard</a></li>
        <li><a href="/user/fartoy_sok.php">Søk i fartøy</a></li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADM'): ?>
            <li><a href="#">Administrer Data</a></li>
            <li><a href="#">Brukeradministrasjon</a></li>
        <?php endif; ?>
        <li><a href="/logout.php">Logg ut</a></li>
    </ul>
</nav>
