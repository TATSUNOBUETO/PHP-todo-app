<!-- includes/header.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ToDoアプリ</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <header class="header">
    <div class="header__inner">
      <h1 class="header__title">ToDoアプリ</h1>
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="header__user">ログイン中: <?= htmlspecialchars($_SESSION['user_id']) ?></div>
      <?php endif; ?>
    </div>
  </header>
