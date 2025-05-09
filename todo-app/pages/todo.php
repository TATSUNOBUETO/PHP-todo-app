<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['add']) && !empty($_POST['title'])) {
    $stmt = $pdo->prepare('INSERT INTO todos (user_id, title) VALUES (?, ?)');
    $stmt->execute([$userId, $_POST['title']]);
  } elseif (isset($_POST['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM todos WHERE id = ? AND user_id = ?');
    $stmt->execute([$_POST['id'], $userId]);
  } elseif (isset($_POST['update'])) {
    $stmt = $pdo->prepare('UPDATE todos SET title = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$_POST['title'], $_POST['id'], $userId]);
  }
}

$todos = $pdo->prepare('SELECT * FROM todos WHERE user_id = ?');
$todos->execute([$userId]);
$data = $todos->fetchAll();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="todo">
  <div class="todo__container">
    <h2 class="todo__title">ToDo リスト</h2>

    <form method="post" class="todo__form">
      <input type="text" name="title" class="todo__input" placeholder="新しいToDoを入力" required>
      <button type="submit" name="add" class="todo__add-button">追加</button>
    </form>

    <ul class="todo__list">
      <?php foreach ($data as $todo): ?>
        <li class="todo__item">
          <form method="post" class="todo__item-form">
            <input type="hidden" name="id" value="<?= $todo['id'] ?>">
            <input type="text" name="title" value="<?= htmlspecialchars($todo['title']) ?>" class="todo__edit-input">
            <button type="submit" name="update" class="todo__update-button">修正</button>
            <button type="submit" name="delete" class="todo__delete-button">削除</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
