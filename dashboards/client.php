<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

$currentRole = requireRole($pdo, ['client', 'admin', 'expert'], '../auth/login.php', 'client.php');

if ($currentRole === 'expert') {
    redirect('expert.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRdv = (int) ($_POST['id_rdv'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    $stmt = $pdo->prepare('SELECT id_expert FROM RENDEZ_VOUS WHERE id_rdv = ? AND id_client = ?');
    $stmt->execute([$idRdv, $_SESSION['id_user']]);
    $rdvMessage = $stmt->fetch();

    if (!$rdvMessage) {
        redirect('client.php', 'error', 'Rendez-vous introuvable.');
    }

    if ($message === '') {
        redirect('client.php?id_rdv=' . $idRdv, 'error', 'Le message est vide.');
    }

    $stmt = $pdo->prepare('
        INSERT INTO MESSAGE_CHAT (id_rdv, id_expediteur, id_destinataire, message)
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$idRdv, $_SESSION['id_user'], $rdvMessage['id_expert'], $message]);

    redirect('client.php?id_rdv=' . $idRdv, 'success', 'Message envoyé.');
}

$stmt = $pdo->prepare('
    SELECT r.*, u.prenom AS expert_prenom, u.nom AS expert_nom, u.email AS expert_email
    FROM RENDEZ_VOUS r
    INNER JOIN UTILISATEUR u ON u.id_user = r.id_expert
    WHERE r.id_client = ?
    ORDER BY r.date_rdv DESC, r.heure DESC
');
$stmt->execute([$_SESSION['id_user']]);
$rdvs = $stmt->fetchAll();

$selectedRdvId = (int) ($_GET['id_rdv'] ?? 0);

if ($selectedRdvId === 0 && !empty($rdvs)) {
    $selectedRdvId = (int) $rdvs[0]['id_rdv'];
}

$selectedRdv = null;

foreach ($rdvs as $rdv) {
    if ((int) $rdv['id_rdv'] === $selectedRdvId) {
        $selectedRdv = $rdv;
        break;
    }
}

$messages = [];

if ($selectedRdv) {
    $stmt = $pdo->prepare('
        SELECT m.*, u.prenom, u.nom
        FROM MESSAGE_CHAT m
        INNER JOIN UTILISATEUR u ON u.id_user = m.id_expediteur
        WHERE m.id_rdv = ?
        ORDER BY m.date_message ASC
    ');
    $stmt->execute([$selectedRdvId]);
    $messages = $stmt->fetchAll();
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 md:px-10 md:py-12">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Espace client</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Bienvenue sur votre dashboard</h1>
            <p class="mt-4 font-hatton text-main">
                Vous êtes connecté avec l'email : <?= htmlspecialchars($_SESSION['email']) ?>
            </p>
        </div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[0.75fr_1.25fr]">
            <aside class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Rendez-vous</p>
                <h2 class="mt-2 font-hatton text-3xl text-main">Mes experts</h2>

                <div class="mt-6 space-y-3">
                    <?php if (empty($rdvs)): ?>
                        <p class="font-hatton text-main">Vous n'avez pas encore de rendez-vous.</p>
                    <?php endif; ?>

                    <?php foreach ($rdvs as $rdv): ?>
                        <div
                            class="rounded-[28px] border border-[#CBB59D] px-5 py-4 font-hatton text-main <?= (int) $rdv['id_rdv'] === $selectedRdvId ? 'bg-button' : 'bg-[#EEE6DC]' ?>">
                            <span class="block text-xl"><?= htmlspecialchars($rdv['expert_prenom'] . ' ' . $rdv['expert_nom']) ?></span>
                            <span class="block text-sm"><?= htmlspecialchars($rdv['service']) ?></span>
                            <span class="block text-sm"><?= htmlspecialchars($rdv['date_rdv'] . ' à ' . substr($rdv['heure'], 0, 5)) ?></span>
                            <span class="mt-2 inline-block rounded-full px-3 py-1 text-sm <?= $rdv['statut'] === 'annule' ? 'bg-red-50 text-red-700' : 'bg-[#DDEEDC] text-main' ?>">
                                <?= $rdv['statut'] === 'annule' ? 'Annulé' : 'Confirmé' ?>
                            </span>
                            <div class="mt-3 flex gap-x-4">
                                <a href="client.php?id_rdv=<?= htmlspecialchars($rdv['id_rdv']) ?>" class="underline hover:no-underline">Voir le chat</a>
                                <?php if ($rdv['mode_paiement'] === 'Paiement sur place' && $rdv['statut'] !== 'annule'): ?>
                                    <a href="../includes/generate_rdv_pdf.php?id_rdv=<?= htmlspecialchars($rdv['id_rdv']) ?>" target="_blank" class="underline hover:no-underline">Télécharger PDF</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>

            <section class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8">
                <?php if ($selectedRdv): ?>
                    <div class="mb-6">
                        <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Chat</p>
                        <h2 class="mt-2 font-hatton text-3xl text-main">
                            <?= htmlspecialchars($selectedRdv['expert_prenom'] . ' ' . $selectedRdv['expert_nom']) ?>
                        </h2>
                        <p class="mt-2 font-hatton text-main">
                            <?= htmlspecialchars($selectedRdv['expert_email']) ?> - <?= htmlspecialchars($selectedRdv['service']) ?>
                        </p>
                        <p class="mt-2 font-hatton text-main">
                            Statut : <?= $selectedRdv['statut'] === 'annule' ? 'annulé' : 'confirmé' ?>
                        </p>
                    </div>

                    <div class="h-[360px] overflow-y-auto rounded-[30px] bg-[#EEE6DC] p-5">
                        <?php if (empty($messages)): ?>
                            <p class="font-hatton text-main">Aucun message pour ce rendez-vous.</p>
                        <?php endif; ?>

                        <?php foreach ($messages as $message): ?>
                            <?php $isMine = (int) $message['id_expediteur'] === (int) $_SESSION['id_user']; ?>
                            <div class="mb-4 flex <?= $isMine ? 'justify-end' : 'justify-start' ?>">
                                <div class="max-w-[80%] rounded-[24px] px-5 py-4 font-hatton <?= $isMine ? 'bg-button text-main' : 'bg-white text-main' ?>">
                                    <p class="text-sm"><?= htmlspecialchars($message['prenom'] . ' ' . $message['nom']) ?></p>
                                    <p class="mt-1"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                    <p class="mt-2 text-xs"><?= htmlspecialchars($message['date_message']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form action="client.php?id_rdv=<?= htmlspecialchars($selectedRdv['id_rdv']) ?>" method="post" class="mt-5">
                        <input type="hidden" name="id_rdv" value="<?= htmlspecialchars($selectedRdv['id_rdv']) ?>">
                        <label for="message" class="font-hatton text-main">Message</label>
                        <textarea id="message" name="message" rows="4"
                            class="mt-2 w-full rounded-[24px] border border-[#CBB59D] bg-[#EEE6DC] px-5 py-4 font-hatton text-main"></textarea>
                        <button type="submit" class="mt-3 rounded-full bg-button px-6 py-3 font-hatton text-main">
                            Envoyer
                        </button>
                    </form>
                <?php else: ?>
                    <p class="font-hatton text-main">Prenez un rendez-vous pour discuter avec un expert.</p>
                <?php endif; ?>
            </section>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
