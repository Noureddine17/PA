<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/fpdf19/fpdf.php');

if (!isset($_GET['id_rdv']) || !is_numeric($_GET['id_rdv'])) {
    require_once(__DIR__ . '/../config/functions.php');
    redirect('../pages/rdv.php');
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
    require_once(__DIR__ . '/../config/functions.php');
    redirect('../pages/rdv.php');
}

$splithour = explode(':', $rdv['heure']);
$heure = $splithour[0] . ':' . $splithour[1];

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, mb_convert_encoding('Récapitulatif de votre rendez-vous KAESKIN', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, mb_convert_encoding('Informations du client :', 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Nom : ' . $rdv['client_prenom'] . ' ' . $rdv['client_nom'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Email : ' . $rdv['client_email'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Ln(5);
$pdf->Cell(0, 10, mb_convert_encoding('Informations du rendez-vous :', 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Service : ' . $rdv['service'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Expert : ' . $rdv['expert_prenom'] . ' ' . $rdv['expert_nom'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Date : ' . $rdv['date_rdv'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Heure : ' . $heure, 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Durée : ' . $rdv['duree'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Prix : ' . number_format($rdv['prix'], 2, ',', '.'), 'ISO-8859-1', 'UTF-8') . "\x80", 0, 1);
$pdf->Cell(0, 7, mb_convert_encoding('Mode de paiement : ' . $rdv['mode_paiement'], 'ISO-8859-1', 'UTF-8'), 0, 1);
$pdf->Ln(10);

$pdf->Output('I', 'rdv_kaeskin_' . $idRdv . '.pdf');

exit;
