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


$stmtUser = $pdo->prepare('SELECT is_banned FROM UTILISATEUR WHERE id_user = ?');
$stmtUser->execute([$_SESSION['id_user']]);
$user = $stmtUser->fetch();
if ($user && $user['is_banned']) {
    redirect('blog.php', 'error', 'Vous êtes banni et ne pouvez pas effectuer cette action.');
}

$isAdmin = isCurrentAdmin($pdo);
$stmt = $pdo->prepare('SELECT article_id, id_user FROM BLOG_COMMENT WHERE id_comment = ?');
$stmt->execute([$commentId]);
$comment = $stmt->fetch();

if (!$comment) {
    redirect('blog.php', 'error', 'Commentaire introuvable.');
}

if ($comment['id_user'] != $_SESSION['id_user'] && !$isAdmin) {
    redirect('blog.php?article=' . $comment['article_id'], 'error', 'Vous n\'avez pas les droits pour supprimer ce commentaire.');
}

$stmt = $pdo->prepare('DELETE FROM BLOG_COMMENT WHERE id_comment = ?');

if ($stmt->execute([$commentId])) {
    redirect('blog.php?article=' . $comment['article_id'], 'success', 'Commentaire supprime.');
}

redirect('blog.php?article=' . $comment['article_id'], 'error', 'Erreur lors de la suppression.');
?>
