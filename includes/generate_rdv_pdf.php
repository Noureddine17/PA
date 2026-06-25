<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/fpdf19/fpdf.php');

if (!isset($_GET['id_rdv']) || !is_numeric($_GET['id_rdv'])) {
    header('Location: ../pages/rdv.php');
    exit;
}

$idRdv = (int)$_GET['id_rdv'];

$stmt = $pdo->prepare('
    SELECT
        R.id_rdv,
        R.service,
        R.date_rdv,
        R.heure,
        R.duree,
        R.prix,
        R.mode_paiement,
        C.prenom AS client_prenom,
        C.nom AS client_nom,
        C.email AS client_email,
        E.prenom AS expert_prenom,
        E.nom AS expert_nom
    FROM RENDEZ_VOUS R
    JOIN UTILISATEUR C ON R.id_client = C.id_user
    JOIN UTILISATEUR E ON R.id_expert = E.id_user
    WHERE R.id_rdv = ?
');
$stmt->execute([$idRdv]);
$rdv = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rdv) {
    header('Location: ../pages/rdv.php');
    exit;
}

$splithour = explode(':', $rdv['heure']);
$heure = $splithour[0]. ':' . $splithour[1];

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10 , utf8_decode('Récapitulatif de votre rendez-vous KAESKIN'), 1, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Informations du client :'), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Nom : ' . $rdv['client_prenom'] . ' ' . $rdv['client_nom']), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Email : ' . $rdv['client_email']), 0, 1);
$pdf->Ln(5);

$pdf->Cell(0, 10, utf8_decode('Informations du rendez-vous :'), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Service : ' . $rdv['service']), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Expert : ' . $rdv['expert_prenom'] . ' ' . $rdv['expert_nom']), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Date : ' . $rdv['date_rdv']), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Heure : ' . $heure), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Durée : ' . $rdv['duree']), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Prix : ' . number_format($rdv['prix'], 2, ',', '.') . ' €'), 0, 1);
$pdf->Cell(0, 7, utf8_decode('Mode de paiement : ' . $rdv['mode_paiement']), 0, 1);
$pdf->Ln(10);

$pdf->Output('I', 'rdv_kaeskin_' . $idRdv . '.pdf');
exit;
?>
