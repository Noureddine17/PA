<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

$email = $_SESSION['email'];
$service = $data['service'] ?? '';
$expert = $data['expert'] ?? '';
$date = $data['date'] ?? '';
$heure = $data['heure'] ?? '';
$duree = $data['duree'] ?? '';
$prix = $data['prix'] ?? '';

$message = "Bonjour,\n\nVotre rendez-vous KAESKIN est confirmé.\n\nSoin : $service\nExpert : $expert\nDate : $date\nHeure : $heure\nDurée : $duree\nPrix : $prix\nPaiement : sur place\n\nA bientôt chez KAESKIN.";

$mailEnvoye = sendMail($email, 'Confirmation de votre rendez-vous KAESKIN', $message);

if ($mailEnvoye) {
    echo json_encode(['success' => true, 'message' => 'Mail de confirmation envoyé.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Le mail n’a pas pu être envoyé.']);
}
