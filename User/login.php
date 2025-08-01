<?php
session_start();
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password_hash"])) {
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["role"] = $user["role"];
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Feil brukernavn eller passord.";
    }
}
?>

<form method="post">
    E-post: <input type="email" name="email" required><br>
    Passord: <input type="password" name="password" required><br>
    <button type="submit">Logg inn</button>
</form>
