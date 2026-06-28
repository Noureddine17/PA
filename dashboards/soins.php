<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

requireRole($pdo, 'admin', '../auth/login.php', 'client.php', 'Accès réservé aux administrateurs.');

$soinId = (int) ($_GET['id'] ?? 0);
$soin = [
    'id_soin' => 0,
    'libelle' => '',
    'description' => '',
    'duree' => '',
    'prix' => '',
];

if ($soinId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM SOIN WHERE id_soin = ?');
    $stmt->execute([$soinId]);
    $soinFound = $stmt->fetch();

    if (!$soinFound) {
        redirect('soins.php', 'error', 'Soin introuvable.');
    }

    $soin = $soinFound;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idSoin = (int) ($_POST['id_soin'] ?? 0);

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM SOIN WHERE id_soin = ?');
        $stmt->execute([$idSoin]);

        redirect('soins.php', 'success', 'Soin supprimé.');
    }

    $libelle = trim($_POST['libelle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duree = (int) ($_POST['duree'] ?? 0);
    $prix = (float) ($_POST['prix'] ?? 0);

    if ($libelle === '' || $prix <= 0 || $duree <= 0) {
        redirect('soins.php' . ($idSoin > 0 ? '?id=' . $idSoin : ''), 'error', 'Les champs principaux sont obligatoires.');
    }

    try {
        if ($action === 'save' && $idSoin > 0) {
            $stmt = $pdo->prepare('
                UPDATE SOIN
                SET libelle = ?, description = ?, duree = ?, prix = ?
                WHERE id_soin = ?
            ');
            $stmt->execute([$libelle, $description, $duree, $prix, $idSoin]);

            redirect('soins.php', 'success', 'Soin modifié.');
        }

        $stmt = $pdo->prepare('
            INSERT INTO SOIN (libelle, description, duree, prix)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$libelle, $description, $duree, $prix]);
    } catch (PDOException $e) {
        redirect('soins.php' . ($idSoin > 0 ? '?id=' . $idSoin : ''), 'error', 'Un soin avec ce nom existe déjà.');
    }

    redirect('soins.php', 'success', 'Soin ajouté.');
}

$stmt = $pdo->query('SELECT * FROM SOIN ORDER BY id_soin DESC');
$soins = $stmt->fetchAll();

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 md:px-10 md:py-12">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Admin</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Gestion des soins</h1>
            <p class="mt-4 font-hatton text-main">
                Ajout, modification et suppression des soins proposés.
            </p>
            <a href="admin.php" class="mt-5 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Retour dashboard
            </a>
        </div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">
                    <?= $soinId > 0 ? 'Modifier' : 'Ajouter' ?>
                </p>
                <h2 class="mt-2 font-hatton text-3xl text-main">
                    <?= $soinId > 0 ? 'Modifier un soin' : 'Nouveau soin' ?>
                </h2>

                <form action="soins.php<?= $soinId > 0 ? '?id=' . htmlspecialchars($soinId) : '' ?>" method="post" class="mt-6 space-y-4">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id_soin" value="<?= htmlspecialchars($soin['id_soin'] ?? 0) ?>">

                    <div>
                        <label for="libelle" class="font-hatton text-main">Libellé</label>
                        <input type="text" id="libelle" name="libelle" value="<?= htmlspecialchars($soin['libelle'] ?? '') ?>"
                            class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="prix" class="font-hatton text-main">Prix</label>
                            <input type="number" step="0.01" id="prix" name="prix" value="<?= htmlspecialchars($soin['prix'] ?? '') ?>"
                                class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                        </div>
                        <div>
                            <label for="duree" class="font-hatton text-main">Durée (en minutes)</label>
                            <input type="number" id="duree" name="duree" value="<?= htmlspecialchars($soin['duree'] ?? '') ?>"
                                class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                        </div>
                    </div>

                    <div>
                        <label for="description" class="font-hatton text-main">Description</label>
                        <textarea id="description" name="description" rows="5"
                            class="mt-2 w-full rounded-[24px] border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main"><?= htmlspecialchars($soin['description'] ?? '') ?></textarea>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-3">
                        <button type="submit" class="rounded-full bg-button px-6 py-3 font-hatton text-main">
                            Enregistrer
                        </button>
                        <a href="soins.php" class="rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                            Nouveau
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Soins</p>
                <h2 class="mt-2 font-hatton text-3xl text-main">Liste des soins</h2>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full border-collapse font-hatton text-main">
                        <thead>
                            <tr class="border-b border-[#CBB59D] text-left">
                                <th class="px-4 py-3">Libellé</th>
                                <th class="px-4 py-3">Durée</th>
                                <th class="px-4 py-3">Prix</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($soins as $item): ?>
                                <tr class="border-b border-[#E8E2D9]">
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['libelle']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['duree']) ?> min</td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['prix']) ?> €</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-2">
                                            <a href="soins.php?id=<?= htmlspecialchars($item['id_soin']) ?>"
                                                class="rounded-full bg-button px-4 py-2 text-center font-hatton text-main">
                                                Modifier
                                            </a>
                                            <form action="soins.php" method="post" onsubmit="return confirm('Supprimer ce soin ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_soin" value="<?= htmlspecialchars($item['id_soin']) ?>">
                                                <button type="submit" class="rounded-full border border-red-300 px-4 py-2 font-hatton text-red-700">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
