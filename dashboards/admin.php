<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

requireRole($pdo, 'admin', '../auth/login.php', 'client.php', 'Accès réservé aux administrateurs.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['id_user'] ?? 0);

    if ($action === 'delete') {
        if ($userId === (int) $_SESSION['id_user']) {
            redirect('admin.php', 'error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('DELETE FROM NEWSLETTER WHERE id_user = ?');
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare('DELETE FROM COMMANDE WHERE id_user = ?');
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare('DELETE FROM UTILISATEUR WHERE id_user = ?');
        $stmt->execute([$userId]);

        $pdo->commit();

        redirect('admin.php', 'success', 'Utilisateur supprimé.');
    }
}

$stmt = $pdo->query('
    SELECT u.id_user, u.nom, u.prenom, u.email, u.role, u.verif_email, u.date_inscription, u.derniere_activite, u.is_banned, n.id_newsletter AS newsletter
    FROM UTILISATEUR u
    LEFT JOIN NEWSLETTER n ON n.id_user = u.id_user
    ORDER BY u.id_user DESC
');
$users = $stmt->fetchAll();

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 md:px-10 md:py-12">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Espace admin</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Dashboard administrateur</h1>
            <p class="mt-4 font-hatton text-main">
                Vous êtes connecté en admin avec l'email : <?= htmlspecialchars($_SESSION['email']) ?>
            </p>
            <a href="products.php" class="mt-5 inline-block rounded-full bg-button px-6 py-3 font-hatton text-main">
                Gérer les produits du shop
            </a>
            <a href="soins.php" class="mt-5 ml-3 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Gérer les soins
            </a>
            <a href="creneaux.php" class="mt-5 ml-3 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Gérer les créneaux RDV
            </a>
            <a href="client.php" class="mt-5 ml-3 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Mes rendez-vous / chat
            </a>
            <a href="../pages/captcha.php" class="mt-5 ml-3 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Ajouter des images pour le captcha
            </a>
            <a href="send_newsletter.php" class="mt-5 ml-3 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Envoyer une newsletter
            </a>
        </div>

        <div class="mt-8 rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
            <div class="mb-6">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Utilisateurs</p>
                <h2 class="mt-2 font-hatton text-3xl text-main">Liste des comptes</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse font-hatton text-main">
                    <thead>
                        <tr class="border-b border-[#CBB59D] text-left">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Nom</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Newsletter</th>
                            <th class="px-4 py-3">Email vérifié</th>
                            <th class="px-4 py-3"> Date d'inscription</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                           <?php $date_from_db = $user['date_inscription'];
                                $timestamp = strtotime($date_from_db);?>
                            <tr class="border-b border-[#E8E2D9]">
                                <td class="px-4 py-3"><?= htmlspecialchars($user['id_user']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <?php if ($user['is_banned']): ?>
                                        <span class="rounded-full bg-red-100 px-3 py-1 font-hatton text-sm text-red-800">
                                            Banni
                                        </span>
                                    <?php else: ?>
                                        <?php
                                        $isOnline = false;
                                        if ($user['derniere_activite']) {
                                            $lastActivity = strtotime($user['derniere_activite']);
                                            $currentTime = time();
                                            if (($currentTime - $lastActivity) < 300) {
                                                $isOnline = true;
                                            }
                                        }
                                        ?>
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block h-2.5 w-2.5 rounded-full <?= $isOnline ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                                            <?= $isOnline ? 'En ligne' : 'Hors ligne' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($user['role']) ?></td>
                                <td class="px-4 py-3"><?= $user['newsletter'] ? 'Oui' : 'Non' ?></td>
                                <td class="px-4 py-3"><?= $user['verif_email'] ? 'Oui' : 'Non' ?></td>
                                <td class="px-4 py-3"><?= date('d/m/Y', $timestamp) ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col items-start gap-2">
                                        <a href="update_user.php?id=<?= htmlspecialchars($user['id_user']) ?>" class="rounded-full bg-button px-4 py-2 text-center font-hatton text-main">
                                            Modifier
                                        </a>

                                        <?php if ($user['id_user'] != $_SESSION['id_user']): ?>
                                            <?php if (!$user['is_banned']): ?>
                                                <form action="../pages/ban_user.php" method="post" onsubmit="return confirm('Voulez-vous vraiment bannir cet utilisateur ?');">
                                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id_user']) ?>">
                                                    <button type="submit" class="w-full rounded-full border border-yellow-600 bg-yellow-50 px-4 py-2 font-hatton text-yellow-800">
                                                        Bannir
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="admin.php" method="post" onsubmit="return confirm('Tu es sûr de vouloir supprimer cet utilisateur ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_user" value="<?= htmlspecialchars($user['id_user']) ?>">
                                                <button type="submit" class="w-full rounded-full border border-red-300 bg-red-50 px-4 py-2 font-hatton text-red-700">
                                                    Supprimer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
