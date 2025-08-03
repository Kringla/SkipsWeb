<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'USR';
    $errors = [];

    // Validering av input
    if (empty($email) || empty($password) || empty($confirm)) {
        $errors[] = 'Alle felter må fylles ut.';
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ugyldig e-postadresse.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Passordet må ha minst 6 tegn.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passord og bekreftelse er ikke like.';
        }
        // Tillat kun gyldige roller (standard til 'USR' hvis ugyldig)
        $role = ($role === 'ADM' ? 'ADM' : 'USR');
    }

    if (!$errors) {
        // Sjekk om e-post allerede finnes
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'E-postadressen er allerede registrert.';
        }
        $stmt->close();
    }

    if (!$errors) {
        // Hash passordet før lagring
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $hash, $role);
        if ($stmt->execute()) {
            // Vellykket registrering – send bruker til innlogging
            header('Location: /login.php');
            exit;
        } else {
            $errors[] = 'Kunne ikke registrere bruker (databasefeil).';
            error_log("Failed to register user $email: " . $stmt->error);
        }
        $stmt->close();
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="register-form">
    <h2>Registrer ny bruker</h2>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="register.php">
        <label for="email">E-post:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Passord:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Bekreft passord:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <label for="role">Rolle:</label>
        <select id="role" name="role">
            <option value="USR">Bruker</option>
            <option value="ADM">Administrator</option>
        </select>

        <button type="submit">Registrer</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

