<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../includes/product_card.php');

header('Content-Type: text/html; charset=UTF-8');

$isAdmin = false;

if (isset($_SESSION['id_user'])) {
    $stmt = $pdo->prepare('SELECT role FROM UTILISATEUR WHERE id_user = ?');
    $stmt->execute([$_SESSION['id_user']]);
    $currentUser = $stmt->fetch();

    if ($currentUser && $currentUser['role'] === 'admin') {
        $isAdmin = true;
        $_SESSION['role'] = 'admin';
    }
}

$search = trim($_GET['search'] ?? '');

$sql = '
    SELECT
        id_produit AS id,
        nom AS name,
        type_produit AS type,
        prix AS price,
        subtitle,
        description,
        image
    FROM PRODUIT
';

if ($search !== '') {
    $sql .= '
        WHERE nom LIKE ?
        OR type_produit LIKE ?
        OR description LIKE ?
    ';
    $sql .= ' ORDER BY id_produit';

    $searchSql = '%' . $search . '%';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchSql, $searchSql, $searchSql]);
} else {
    $sql .= ' ORDER BY id_produit';
    $stmt = $pdo->query($sql);
}

$products = $stmt->fetchAll();

foreach ($products as $product) {
    afficherCarteProduit($product, $isAdmin);
}
