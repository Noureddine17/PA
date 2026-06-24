<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');

header('Content-Type: application/json');

$expertId = (int) ($_GET['expert_id'] ?? 0);
$date = $_GET['date'] ?? '';
$dateChoisie = DateTime::createFromFormat('Y-m-d', $date);
$timezone = new DateTimeZone('Europe/Paris');
$maintenant = new DateTime('now', $timezone);
$creneauxPasses = [];

if ($expertId <= 0 || !$dateChoisie || $dateChoisie->format('Y-m-d') !== $date) {
    echo json_encode(['success' => false, 'slots' => [], 'reserved' => [], 'unavailable' => []]);
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

echo json_encode([
    'success' => true,
    'slots' => $creneauxPossibles,
    'reserved' => array_column($stmt->fetchAll(), 'heure'),
    'unavailable' => $creneauxPasses
]);
