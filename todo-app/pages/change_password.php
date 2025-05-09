<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($_POST['current_password'], $user['password'])) {
        $error = '現在のパスワードが正しくありません。';
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $error = '新しいパスワードと確認が一致しません。';
    } else {
        $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$newHash, $_SESSION['user_id']]);
        $success = 'パスワードが変更されました。';
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="password">
  <div class="password__box">
    <h2 class="password__title">パスワード変更</h2>

    <?php if ($error): ?>
      <div class="password__error"><?= $error ?></div>
    <?php elseif ($success): ?>
      <div class="password__success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" class="password__form">
      <div class="password__field">
        <label for="current_password" class="password__label">現在のパスワード</label>
        <input type="password" id="current_password" name="current_password" class="password__input" required>
      </div>
      <div class="password__field">
        <label for="new_password" class="password__label">新しいパスワード</label>
        <input type="password" id="new_password" name="new_password" class="password__input" required>
      </div>
      <div class="password__field">
        <label for="confirm_password" class="password__label">新しいパスワード（確認）</label>
        <input type="password" id="confirm_password" name="confirm_password" class="password__input" required>
      </div>
      <button type="submit" class="password__button">変更する</button>
    </form>
  </div>
</div>

