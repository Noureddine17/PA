<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('blog.php');
}

$commentId = (int)($_POST['comment_id'] ?? 0);

if ($commentId === 0) {
    redirect('blog.php', 'error', 'ID invalide.');
}

if (!isset($_SESSION['id_user'])) {
    redirect('../auth/login.php', 'error', 'Vous devez etre connecte.');
}

$stmt = $pdo->prepare('SELECT article_id, id_user FROM BLOG_COMMENT WHERE id_comment = ?');
$stmt->execute([$commentId]);
$comment = $stmt->fetch();

if (!$comment) {
    redirect('blog.php', 'error', 'Commentaire introuvable.');
}

if ($comment['id_user'] != $_SESSION['id_user']) {
    redirect('blog.php?article=' . $comment['article_id'], 'error', 'Vous n\'avez pas les droits pour supprimer ce commentaire.');
}

$stmt = $pdo->prepare('DELETE FROM BLOG_COMMENT WHERE id_comment = ?');

if ($stmt->execute([$commentId])) {
    redirect('blog.php?article=' . $comment['article_id'], 'success', 'Commentaire supprime.');
}

redirect('blog.php?article=' . $comment['article_id'], 'error', 'Erreur lors de la suppression.');
?>
