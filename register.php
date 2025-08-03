<?php
// register.php
require_once __DIR__ . '/config/config.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Feil: Ingen databaseforbindelse funnet. Sjekk config.php!");
}

session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Valider input
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';

    if (!$email)            $errors[] = 'Ugyldig e-post.';
    if (empty($password))   $errors[] = 'Passord kan ikke være tomt.';
    if ($password !== $confirm) $errors[] = 'Passordene matcher ikke.';

    // 2) Sjekk om e-post allerede finnes
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM tblzUser WHERE email = ?");
        if (!$stmt) {
            die("Database-feil (prepare): " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'E-postadressen er allerede registrert.';
        }
        $stmt->close();
    }

    // 3) Sett inn ny bruker
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("
            INSERT INTO tblzUser (email, password, role)
            VALUES (?, ?, ?)
        ");
        if (!$stmt) {
            die("Database-feil (prepare insert): " . $conn->error);
        }
        $stmt->bind_param("sss", $email, $hash, $role);
        if ($stmt->execute()) {
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Kunne ikke registrere bruker.';
            error_log("Failed to register user $email: " . $stmt->error);
        }
        $stmt->close();
    }
}
?>

<!-- HTML-skjema -->
<h2>Registrer ny bruker</h2>
<?php foreach ($errors as $e): ?>
  <p class="error"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>
<form method="post" action="">
  <label>E-post:<input type="email" name="email" required></label><br>
  <label>Passord:<input type="password" name="password" required></label><br>
  <label>Bekreft passord:<input type="password" name="confirm_password" required></label><br>
  <label>Rolle:
    <select name="role">
      <option value="user">Bruker</option>
      <option value="admin">Administrator</option>
    </select>
  </label><br>
  <button type="submit">Registrer</button>
</form>
