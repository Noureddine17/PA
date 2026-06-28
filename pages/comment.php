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
    redirect('../auth/login.php', 'error', 'Vous devez vous connecter pour commenter.');
}

$stmtUser = $pdo->prepare('SELECT is_banned FROM UTILISATEUR WHERE id_user = ?');
$stmtUser->execute([$_SESSION['id_user']]);
$user = $stmtUser->fetch();
if ($user && $user['is_banned']) {
    redirect('blog.php', 'error', 'Vous êtes banni et ne pouvez pas commenter.');
}

$articleId = (int)($_POST['article_id'] ?? 0);
$contenu = trim($_POST['contenu'] ?? '');

if ($articleId === 0 || $contenu === '') {
    redirect('blog.php', 'error', 'Donnees invalides.');
}

$stmt = $pdo->prepare('SELECT id_article FROM BLOG_ARTICLE WHERE id_article = ?');
$stmt->execute([$articleId]);

if ($stmt->rowCount() === 0) {
    redirect('blog.php', 'error', 'Article non trouve.');
}

$stmt = $pdo->prepare('INSERT INTO BLOG_COMMENT (article_id, id_user, contenu) VALUES (?, ?, ?)');

if ($stmt->execute([$articleId, $_SESSION['id_user'], $contenu])) {
    redirect('blog.php?article=' . $articleId, 'success', 'Commentaire publie !');
}

redirect('blog.php?article=' . $articleId, 'error', 'Erreur lors de la publication.');
?>
