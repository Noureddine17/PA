<?php
session_start();

require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    redirect('login.php', 'error', 'Lien de confirmation invalide.');
}

$stmt = $pdo->prepare('SELECT id_user FROM UTILISATEUR WHERE token_email = ?');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    redirect('login.php', 'error', 'Lien de confirmation invalide.');
}

$stmt = $pdo->prepare('UPDATE UTILISATEUR SET verif_email = 1, token_email = NULL WHERE id_user = ?');
$stmt->execute([$user['id_user']]);

redirect('login.php', 'success', 'Votre email est confirmé, vous pouvez vous connecter.');
