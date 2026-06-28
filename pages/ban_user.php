<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

// Seuls les administrateurs peuvent accéder à cette page
requireRole($pdo, 'admin', '../auth/login.php', '../index.php', 'Accès non autorisé.');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

$userIdToBan = (int)($_POST['user_id'] ?? 0);
$redirectUrl = $_SERVER['HTTP_REFERER'] ?? '../dashboards/admin.php';

if ($userIdToBan <= 0) {
    redirect($redirectUrl, 'error', 'ID utilisateur invalide.');
}

// Un administrateur ne peut pas se bannir lui-même
if ($userIdToBan === (int)$_SESSION['id_user']) {
    redirect($redirectUrl, 'error', 'Vous ne pouvez pas vous bannir vous-même.');
}

try {
    $stmt = $pdo->prepare('UPDATE UTILISATEUR SET is_banned = 1 WHERE id_user = ?');
    $stmt->execute([$userIdToBan]);

    if ($stmt->rowCount() > 0) {
        redirect($redirectUrl, 'success', 'Utilisateur banni avec succès.');
    } else {
        // Peut-être que l'utilisateur n'existe pas ou est déjà banni
        redirect($redirectUrl, 'error', 'Utilisateur introuvable ou déjà banni.');
    }
} catch (PDOException $e) {
    error_log('Erreur de bannissement: ' . $e->getMessage());
    redirect($redirectUrl, 'error', 'Erreur du serveur lors du bannissement.');
}
?>