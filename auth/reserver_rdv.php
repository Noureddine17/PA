<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../pages/rdv.php');
}

requireLogin();

$idClient = (int)$_SESSION['id_user'];
$idExpert = (int)($_POST['expert'] ?? 0);
$service = trim($_POST['service'] ?? '');
$date = trim($_POST['date'] ?? '');
$heure = trim($_POST['slot'] ?? '');
$duree = trim($_POST['duree'] ?? '');
$prixLabel = trim($_POST['prix'] ?? '');
$modePaiement = trim($_POST['payment_mode'] ?? '');
$prixTexte = str_replace('€', '', $prixLabel);
$prixTexte = str_replace(' ', '', $prixTexte);
$prix = (float)str_replace(',', '.', $prixTexte);
$dateChoisie = DateTime::createFromFormat('Y-m-d', $date);
$timezone = new DateTimeZone('Europe/Paris');
$dateHeureChoisie = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $heure, $timezone);
$maintenant = new DateTime('now', $timezone);

if ($idExpert <= 0 || $service === '' || $duree === '' || $prix <= 0 || $modePaiement === '' || $heure === '') {
    redirect('../pages/rdv.php', 'error', 'Informations du rendez-vous incomplètes.');
}

if (!$dateChoisie || $dateChoisie->format('Y-m-d') !== $date) {
    redirect('../pages/rdv.php', 'error', 'Date ou horaire invalide.');
}

$stmt = $pdo->prepare('SELECT id_creneau FROM CRENEAU_RDV WHERE heure = ? AND actif = 1 LIMIT 1');
$stmt->execute([$heure . ':00']);
$creneauExiste = $stmt->fetch();
if (!$creneauExiste) {
    redirect('../pages/rdv.php', 'error', 'Ce créneau n’est pas disponible.');
}

if (!$dateHeureChoisie || $dateHeureChoisie <= $maintenant) {
    redirect('../pages/rdv.php', 'error', 'Ce créneau est déjà passé.');
}

$stmt = $pdo->prepare("SELECT id_user, prenom, nom FROM UTILISATEUR WHERE id_user = ? AND role = 'expert'");
$stmt->execute([$idExpert]);
$expert = $stmt->fetch();
if (!$expert) {
    redirect('../pages/rdv.php', 'error', 'Expert introuvable.');
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
    redirect('../pages/rdv.php', 'error', 'Ce créneau est déjà réservé.');
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO RENDEZ_VOUS (id_client, id_expert, service, date_rdv, heure, duree, prix, mode_paiement)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$idClient, $idExpert, $service, $date, $heure, $duree, $prix, $modePaiement]);
    $idRdv = (int)$pdo->lastInsertId();
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        redirect('../pages/rdv.php', 'error', 'Ce créneau vient d’être réservé.');
    }
    redirect('../pages/rdv.php', 'error', 'Erreur lors de la réservation.');
}

$expertName = trim($expert['prenom'] . ' ' . $expert['nom']);

if ($modePaiement === 'Paiement sur place') {
    $message = "Bonjour,\n\nVotre rendez-vous KAESKIN est confirmé.\n\nSoin : $service\nExpert : $expertName\nDate : $date\nHeure : $heure\nDurée : $duree\nPrix : $prixLabel\nPaiement : sur place\n\nA bientôt chez KAESKIN.";
    sendMail($_SESSION['email'], 'Confirmation de votre rendez-vous KAESKIN', $message);
    redirect('../pages/rdv.php', 'success', 'Rendez-vous confirmé ! Un e-mail vous a été envoyé.');
} elseif ($modePaiement === 'Paiement en ligne') {
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    $_SESSION['panier']['rdv_' . $idRdv] = [
        'id' => 'rdv_' . $idRdv,
        'name' => $service,
        'type' => 'Rendez-vous',
        'subtitle' => "Le $date à $heure avec $expertName",
        'price' => $prix,
        'quantity' => 1,
        'image' => 'assets/images/services/droplet.svg',
        'rdv_id' => $idRdv
    ];
    redirect('../pages/panier.php');
}
