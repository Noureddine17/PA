<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('blog.php');
}

if (!isset($_SESSION['id_user'])) {
    redirect('../auth/login.php', 'error', 'Vous devez vous connecter pour réagir.');
}

$stmtUser = $pdo->prepare('SELECT is_banned FROM UTILISATEUR WHERE id_user = ?');
$stmtUser->execute([$_SESSION['id_user']]);
$user = $stmtUser->fetch();
if ($user && $user['is_banned']) {
    redirect('blog.php', 'error', 'Vous êtes banni et ne pouvez pas effectuer cette action.');
}

$commentId = (int)($_POST['comment_id'] ?? 0);
$articleId = (int)($_POST['article_id'] ?? 0);

if ($commentId === 0 || $articleId === 0) {
    redirect('blog.php', 'error', 'Données invalides.');
}

$stmt = $pdo->prepare('SELECT id_comment FROM BLOG_COMMENT WHERE id_comment = ?');
$stmt->execute([$commentId]);

if ($stmt->rowCount() === 0) {
    redirect('blog.php?article=' . $articleId, 'error', 'Commentaire introuvable.');
}

$stmt = $pdo->prepare('SELECT id_like FROM BLOG_COMMENT_LIKE WHERE comment_id = ? AND id_user = ?');
$stmt->execute([$commentId, $_SESSION['id_user']]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare('DELETE FROM BLOG_COMMENT_LIKE WHERE comment_id = ? AND id_user = ?');
    $stmt->execute([$commentId, $_SESSION['id_user']]);
} else {
    $stmt = $pdo->prepare('INSERT INTO BLOG_COMMENT_LIKE (comment_id, id_user) VALUES (?, ?)');
    $stmt->execute([$commentId, $_SESSION['id_user']]);
}

redirect('blog.php?article=' . $articleId . '#comment-' . $commentId);
?>