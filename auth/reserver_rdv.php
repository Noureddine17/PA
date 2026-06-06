<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez vous connecter.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

$idClient = (int) $_SESSION['id_user'];
$idExpert = (int) ($data['expert_id'] ?? 0);
$service = trim($data['service'] ?? '');
$date = trim($data['date'] ?? '');
$heure = trim($data['heure'] ?? '');
$duree = trim($data['duree'] ?? '');
$prixLabel = trim($data['prix'] ?? '');
$modePaiement = trim($data['payment_mode'] ?? '');
$prixTexte = str_replace('€', '', $prixLabel);
$prixTexte = str_replace(' ', '', $prixTexte);
$prix = (float) str_replace(',', '.', $prixTexte);
$dateChoisie = DateTime::createFromFormat('Y-m-d', $date);
$creneauxPossibles = ['09:00', '10:30', '12:00', '14:00', '15:30', '17:00'];

if ($idExpert <= 0 || $service === '' || $duree === '' || $prix <= 0 || $modePaiement === '') {
    echo json_encode(['success' => false, 'message' => 'Informations du rendez-vous incomplètes.']);
    exit;
}

if (!$dateChoisie || $dateChoisie->format('Y-m-d') !== $date || !in_array($heure, $creneauxPossibles)) {
    echo json_encode(['success' => false, 'message' => 'Date ou horaire invalide.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id_user, prenom, nom FROM UTILISATEUR WHERE id_user = ? AND role = 'expert'");
$stmt->execute([$idExpert]);
$expert = $stmt->fetch();

if (!$expert) {
    echo json_encode(['success' => false, 'message' => 'Expert introuvable.']);
    exit;
}

$stmt = $pdo->prepare('
    SELECT id_rdv
    FROM RENDEZ_VOUS
    WHERE id_expert = ? AND date_rdv = ? AND heure = ? AND statut = ?
    LIMIT 1
');
$stmt->execute([$idExpert, $date, $heure . ':00', 'confirme']);
$rdvExiste = $stmt->fetch();

if ($rdvExiste) {
    echo json_encode(['success' => false, 'message' => 'Ce créneau est déjà réservé.']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO RENDEZ_VOUS (id_client, id_expert, service, date_rdv, heure, duree, prix, mode_paiement)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$idClient, $idExpert, $service, $date, $heure, $duree, $prix, $modePaiement]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo json_encode(['success' => false, 'message' => 'Ce créneau vient déjà d’être réservé.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Erreur lors de la réservation.']);
    exit;
}

$idRdv = (int) $pdo->lastInsertId();
$expertName = trim($expert['prenom'] . ' ' . $expert['nom']);

if ($modePaiement === 'Paiement sur place') {
    $message = "Bonjour,\n\nVotre rendez-vous KAESKIN est confirmé.\n\nSoin : $service\nExpert : $expertName\nDate : $date\nHeure : $heure\nDurée : $duree\nPrix : $prixLabel\nPaiement : sur place\n\nA bientôt chez KAESKIN.";
    sendMail($_SESSION['email'], 'Confirmation de votre rendez-vous KAESKIN', $message);
}

echo json_encode([
    'success' => true,
    'message' => 'Rendez-vous réservé.',
    'id_rdv' => $idRdv,
    'expert_name' => $expertName
]);
