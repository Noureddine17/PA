<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');

header('Content-Type: application/json');

$expertId = (int) ($_GET['expert_id'] ?? 0);
$date = $_GET['date'] ?? '';
$dateChoisie = DateTime::createFromFormat('Y-m-d', $date);

if ($expertId <= 0 || !$dateChoisie || $dateChoisie->format('Y-m-d') !== $date) {
    echo json_encode(['success' => false, 'reserved' => []]);
    exit;
}

$stmt = $pdo->prepare('
    SELECT DATE_FORMAT(heure, "%H:%i") AS heure
    FROM RENDEZ_VOUS
    WHERE id_expert = ? AND date_rdv = ? AND statut = ?
');
$stmt->execute([$expertId, $date, 'confirme']);

echo json_encode([
    'success' => true,
    'reserved' => array_column($stmt->fetchAll(), 'heure')
]);
