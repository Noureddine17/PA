<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('blog.php');
}

$commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$article = $_POST['article_slug'] ?? '';

if ($commentId <= 0) {
    redirect('blog.php');
}

if (!isset($_SESSION['id_user'])) {
    redirect('../auth/login.php', 'error', 'Vous devez être connecté pour supprimer un commentaire.');
}

try {
    $stmt = $pdo->prepare('SELECT id_user, article_slug FROM BLOG_COMMENT WHERE id_comment = ?');
    $stmt->execute([$commentId]);
    $row = $stmt->fetch();

    if (!$row) {
        redirect('blog.php', 'error', 'Commentaire introuvable.');
    }

    if ($row['id_user'] != $_SESSION['id_user']) {
        redirect('blog.php?article=' . urlencode($row['article_slug']) . '#post-' . urlencode($row['article_slug']), 'error', 'Vous n\'avez pas les droits pour supprimer ce commentaire.');
    }

    $del = $pdo->prepare('DELETE FROM BLOG_COMMENT WHERE id_comment = ?');
    $del->execute([$commentId]);

    redirect('blog.php?article=' . urlencode($row['article_slug']) . '#post-' . urlencode($row['article_slug']), 'success', 'Commentaire supprimé.');
} catch (Exception $e) {
    redirect('blog.php', 'error', 'Erreur lors de la suppression.');
}
