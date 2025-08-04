<?php
// login.php
require_once __DIR__ . '/config/config.php';
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email)        $errors[] = 'Ugyldig e-post.';
    if (!$password)     $errors[] = 'Passord kan ikke være tomt.';

    if (empty($errors)) {
        $stmt = $conn->prepare("
            SELECT user_id, password, role
            FROM tblzUser
            WHERE email = ?
        ");
        if (!$stmt) {
            die("Database-feil (prepare): " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($user_id, $hash, $role);
        if ($stmt->fetch() && password_verify($password, $hash)) {
            // Innlogging OK
            $_SESSION['user_id']    = $user_id;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role']  = $role;
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } else {
            $errors[] = 'Feil e-post eller passord.';
        }
        $stmt->close();
    }
}
?>

<!-- HTML-skjema -->
<h2>Logg inn</h2>
<?php foreach ($errors as $e): ?>
  <p class="error"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>
<form method="post" action="">
  <label>E-post:<input type="email" name="email" required></label><br>
  <label>Passord:<input type="password" name="password" required></label><br>
  <button type="submit">Logg inn</button>
</form>
