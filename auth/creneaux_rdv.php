<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

header('Content-Type: application/json');

$expertId = (int) ($_GET['expert_id'] ?? 0);
$date = $_GET['date'] ?? '';
$currentRole = isset($_SESSION['id_user']) ? getCurrentRole($pdo) : null;
$currentUserId = isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 0;
$dateChoisie = DateTime::createFromFormat('Y-m-d', $date);
$timezone = new DateTimeZone('Europe/Paris');
$maintenant = new DateTime('now', $timezone);
$creneauxPasses = [];

if ($expertId <= 0 || !$dateChoisie || $dateChoisie->format('Y-m-d') !== $date) {
    echo json_encode(['success' => false, 'slots' => [], 'reserved' => [], 'unavailable' => []]);
    exit;
}

if ($currentRole === 'expert' && $expertId === $currentUserId) {
    echo json_encode(['success' => true, 'slots' => [], 'reserved' => [], 'unavailable' => []]);
    exit;
}

$stmt = $pdo->query('
    SELECT DATE_FORMAT(heure, "%H:%i") AS heure
    FROM CRENEAU_RDV
    WHERE actif = 1
    ORDER BY heure
');
$creneauxPossibles = array_column($stmt->fetchAll(), 'heure');

foreach ($creneauxPossibles as $creneau) {
    $dateCreneau = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $creneau, $timezone);

    if ($dateCreneau && $dateCreneau <= $maintenant) {
        $creneauxPasses[] = $creneau;
    }
}

$stmt = $pdo->prepare('
    SELECT DATE_FORMAT(heure, "%H:%i") AS heure
    FROM RENDEZ_VOUS
    WHERE id_expert = ? AND date_rdv = ? AND statut = ?
');
$stmt->execute([$expertId, $date, 'confirme']);
$reservedFromDb = array_column($stmt->fetchAll(), 'heure');

$reservedFromSession = [];
if (!empty($_SESSION['panier'])) {
    $rdvIdsInCart = [];
    foreach ($_SESSION['panier'] as $item) {
        if (isset($item['type']) && $item['type'] === 'Rendez-vous' && isset($item['rdv_id'])) {
            $rdvIdsInCart[] = (int)$item['rdv_id'];
        }
    }

    if (!empty($rdvIdsInCart)) {
        $inParams = implode(',', array_fill(0, count($rdvIdsInCart), '?'));
        $stmtSession = $pdo->prepare("
            SELECT DATE_FORMAT(heure, '%H:%i') AS heure
            FROM RENDEZ_VOUS
            WHERE id_rdv IN ($inParams) AND id_expert = ? AND date_rdv = ?
        ");
        $params = array_merge($rdvIdsInCart, [$expertId, $date]);
        $stmtSession->execute($params);
        $reservedFromSession = array_column($stmtSession->fetchAll(), 'heure');
    }
}

$allReserved = array_unique(array_merge($reservedFromDb, $reservedFromSession));

echo json_encode([
    'success' => true,
    'slots' => $creneauxPossibles,
    'reserved' => array_values($allReserved),
    'unavailable' => $creneauxPasses
]);
