<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    $role = 'ADM';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Ugyldig e-post.";
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$email, $hash, $role]);
        echo "Administrator registrert.";
    } catch (PDOException $e) {
        echo "Feil: " . $e->getMessage();
    }
}
?>

<form method="post">
    E-post: <input type="email" name="email" required><br>
    Passord: <input type="password" name="password" required><br>
    <button type="submit">Registrer</button>
</form>
