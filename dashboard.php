<?php
require_once __DIR__ . '/includes/bootstrap.php';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<?php include __DIR__ . '/includes/menu.php'; ?>

<div class="dashboard">
    <h1>Velkommen til SkipsWeb</h1>
    <p>Du er logget inn som <?php echo ($_SESSION['user_role'] === 'admin' ? 'administrator' : 'bruker'); ?>.</p>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
        <p>Som administrator kan du legge til, endre eller slette data og brukere.</p>
    <?php else: ?>
        <p>Som vanlig bruker kan du søke i og lese data.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
