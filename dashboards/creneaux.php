<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

requireRole($pdo, 'admin', '../auth/login.php', 'client.php', 'Accès réservé aux administrateurs.');

$creneauId = (int) ($_GET['id'] ?? 0);
$creneau = [
    'id_creneau' => 0,
    'heure' => '',
    'actif' => 1,
];

if ($creneauId > 0) {
    $stmt = $pdo->prepare('SELECT id_creneau, DATE_FORMAT(heure, "%H:%i") AS heure, actif FROM CRENEAU_RDV WHERE id_creneau = ?');
    $stmt->execute([$creneauId]);
    $creneauTrouve = $stmt->fetch();

    if (!$creneauTrouve) {
        redirect('creneaux.php', 'error', 'Créneau introuvable.');
    }

    $creneau = $creneauTrouve;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idCreneau = (int) ($_POST['id_creneau'] ?? 0);

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM CRENEAU_RDV WHERE id_creneau = ?');
        $stmt->execute([$idCreneau]);

        redirect('creneaux.php', 'success', 'Créneau supprimé.');
    }

    if ($action === 'toggle') {
        $stmt = $pdo->prepare('UPDATE CRENEAU_RDV SET actif = IF(actif = 1, 0, 1) WHERE id_creneau = ?');
        $stmt->execute([$idCreneau]);

        redirect('creneaux.php', 'success', 'Statut du créneau modifié.');
    }

    $heure = trim($_POST['heure'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;
    $heureValide = DateTime::createFromFormat('H:i', $heure);

    if (!$heureValide || $heureValide->format('H:i') !== $heure) {
        redirect('creneaux.php' . ($idCreneau > 0 ? '?id=' . $idCreneau : ''), 'error', 'Heure invalide.');
    }

    try {
        if ($action === 'save' && $idCreneau > 0) {
            $stmt = $pdo->prepare('UPDATE CRENEAU_RDV SET heure = ?, actif = ? WHERE id_creneau = ?');
            $stmt->execute([$heure . ':00', $actif, $idCreneau]);

            redirect('creneaux.php', 'success', 'Créneau modifié.');
        }

        $stmt = $pdo->prepare('INSERT INTO CRENEAU_RDV (heure, actif) VALUES (?, ?)');
        $stmt->execute([$heure . ':00', $actif]);
    } catch (PDOException $e) {
        redirect('creneaux.php' . ($idCreneau > 0 ? '?id=' . $idCreneau : ''), 'error', 'Ce créneau existe déjà.');
    }

    redirect('creneaux.php', 'success', 'Créneau ajouté.');
}

$stmt = $pdo->query('SELECT id_creneau, DATE_FORMAT(heure, "%H:%i") AS heure, actif FROM CRENEAU_RDV ORDER BY heure');
$creneaux = $stmt->fetchAll();

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 md:px-10 md:py-12">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Admin</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Gestion des créneaux</h1>
            <p class="mt-4 font-hatton text-main">
                Ajoutez, modifiez ou désactivez les horaires proposés pour les rendez-vous.
            </p>
            <a href="admin.php" class="mt-5 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Retour dashboard
            </a>
        </div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">
                    <?= $creneauId > 0 ? 'Modifier' : 'Ajouter' ?>
                </p>
                <h2 class="mt-2 font-hatton text-3xl text-main">
                    <?= $creneauId > 0 ? 'Modifier un créneau' : 'Nouveau créneau' ?>
                </h2>

                <form action="creneaux.php<?= $creneauId > 0 ? '?id=' . htmlspecialchars($creneauId) : '' ?>" method="post" class="mt-6 space-y-4">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id_creneau" value="<?= htmlspecialchars($creneau['id_creneau']) ?>">

                    <div>
                        <label for="heure" class="font-hatton text-main">Heure</label>
                        <input type="time" id="heure" name="heure" value="<?= htmlspecialchars($creneau['heure']) ?>"
                            class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main" required>
                    </div>

                    <label class="flex items-center gap-3 font-hatton text-main">
                        <input type="checkbox" name="actif" value="1" class="h-5 w-5 accent-[#B09882]" <?= (int) $creneau['actif'] === 1 ? 'checked' : '' ?>>
                        Créneau actif
                    </label>

                    <div class="flex flex-wrap gap-3 pt-3">
                        <button type="submit" class="rounded-full bg-button px-6 py-3 font-hatton text-main">
                            Enregistrer
                        </button>
                        <a href="creneaux.php" class="rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                            Nouveau
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Créneaux</p>
                <h2 class="mt-2 font-hatton text-3xl text-main">Horaires disponibles</h2>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full border-collapse font-hatton text-main">
                        <thead>
                            <tr class="border-b border-[#CBB59D] text-left">
                                <th class="px-4 py-3">Heure</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($creneaux as $item): ?>
                                <tr class="border-b border-[#E8E2D9]">
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['heure']) ?></td>
                                    <td class="px-4 py-3"><?= (int) $item['actif'] === 1 ? 'Actif' : 'Inactif' ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-2 sm:flex-row">
                                            <a href="creneaux.php?id=<?= htmlspecialchars($item['id_creneau']) ?>"
                                                class="rounded-full bg-button px-4 py-2 text-center font-hatton text-main">
                                                Modifier
                                            </a>
                                            <form action="creneaux.php" method="post">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="id_creneau" value="<?= htmlspecialchars($item['id_creneau']) ?>">
                                                <button type="submit" class="rounded-full border border-[#CBB59D] px-4 py-2 font-hatton text-main">
                                                    <?= (int) $item['actif'] === 1 ? 'Désactiver' : 'Activer' ?>
                                                </button>
                                            </form>
                                            <form action="creneaux.php" method="post" onsubmit="return confirm('Supprimer ce créneau ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_creneau" value="<?= htmlspecialchars($item['id_creneau']) ?>">
                                                <button type="submit" class="rounded-full border border-red-300 px-4 py-2 font-hatton text-red-700">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($creneaux)): ?>
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center">Aucun créneau créé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
