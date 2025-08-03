<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// resten av koden din følger her...

require_once __DIR__ . '/includes/bootstrap.php';
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $errors[] = 'Vennligst fyll inn alle felter.';
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Sjekk passordhash
            if (password_verify($password, $row['password'])) {
                // Innlogging vellykket
                $_SESSION['user_id']   = $row['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role']  = $row['role'];
                // Send til riktig side basert på rolle
                header('Location: /dashboard.php');
                $stmt->close();
                exit;
            } else {
                $errors[] = 'Feil e-post eller passord.';
            }
        } else {
            $errors[] = 'Feil e-post eller passord.';
        }
        $stmt->close();
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="login-form">
    <h2>Logg inn</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="email">E-post:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Passord:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Logg inn</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
