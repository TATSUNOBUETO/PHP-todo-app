<?php
session_start();
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: todo.php');
        exit;
    } else {
        $error = 'ユーザー名またはパスワードが違います。';
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="login">
  <div class="login__box">
    <h2 class="login__title">ログイン</h2>

    <?php if ($error): ?>
      <div class="login__error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="login__form">
      <div class="login__field">
        <label for="username" class="login__label">ユーザー名</label>
        <input type="text" name="username" id="username" class="login__input" required>
      </div>
      <div class="login__field">
        <label for="password" class="login__label">パスワード</label>
        <input type="password" name="password" id="password" class="login__input" required>
      </div>
      <button type="submit" class="login__button">ログイン</button>
    </form>
  </div>
</div>
