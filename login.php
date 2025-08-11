<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM tblzUser WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    } else {
        $error = "Feil e-post eller passord.";
    }
}
?>

<!-- HTML-skjema -->
<form method="POST" action="">
    <input type="email" name="email" placeholder="E-post" required class="input">
    <input type="password" name="password" placeholder="Passord" required class="input">
    <button type="submit" class="btn primary">Logg inn</button>
</form>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
