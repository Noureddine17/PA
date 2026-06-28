<?php
include(__DIR__ . '/../headers/header.php');
require_once(__DIR__ . '/../config/connexion.php');

$isConnected = isset($_SESSION['id_user']);
$currentRole = $isConnected ? getCurrentRole($pdo) : null;
$currentUserId = $isConnected ? (int) $_SESSION['id_user'] : 0;

$stmtExperts = $pdo->prepare("
        SELECT id_user, prenom, nom
        FROM UTILISATEUR
        WHERE role = 'expert'
            AND (? = 0 OR id_user <> ?)
        ORDER BY prenom, nom
");
$stmtExperts->execute([$currentRole === 'expert' ? $currentUserId : 0, $currentUserId]);
$experts = $stmtExperts->fetchAll();
$firstExpertName = empty($experts) ? 'Aucun expert' : trim($experts[0]['prenom'] . ' ' . $experts[0]['nom']);

$stmtSoins = $pdo->query('SELECT id_soin, libelle, description, duree, prix FROM SOIN ORDER BY id_soin DESC');
$soins = $stmtSoins->fetchAll();
?>

<main class="pb-12 md:pb-20">
   

    <section class="container mx-auto px-3 pt-6 sm:px-4 md:pt-12">
        <div class="grid gap-6">
            <form id="rdv-form" action="../auth/reserver_rdv.php" method="POST" class="bg-[#F5F2ED] border border-div rounded-[28px] p-4 shadow-xl/20 sm:rounded-[40px] sm:p-6 md:rounded-[56px] md:p-10">
                <div class="mb-8 md:mb-10">
                    <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                        <div>
                            <p class="font-hatton text-xs uppercase tracking-[0.22em] sm:text-sm sm:tracking-[0.3em]">Étape 1</p>
                            <h2 class="font-hatton text-2xl text-main sm:text-3xl">Choisissez votre soin</h2>
                        </div>
                        <span class="w-fit rounded-full bg-button px-4 py-2 font-hatton text-sm text-main sm:px-5 sm:text-base">Soins disponibles</span>
                    </div>

                    <div class="grid gap-3 md:grid-cols-3 md:gap-4">
                        <?php if (empty($soins)): ?>
                            <p class="col-span-full rounded-[20px] bg-default px-4 py-4 text-center font-hatton text-main">
                                Aucun soin disponible pour le moment.
                            </p>
                        <?php else: ?>
                            <?php foreach ($soins as $soin): ?>
                                <label class="block cursor-pointer">
                                    <input type="radio" name="service" value="<?= htmlspecialchars($soin['libelle']) ?>" class="peer sr-only"
                                        data-duration="<?= htmlspecialchars($soin['duree']) ?> min" data-price="<?= htmlspecialchars(number_format($soin['prix'], 2, '.', '')) ?> €">
                                    <span
                                        class="block rounded-[22px] border border-div bg-default p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E] sm:rounded-[28px] sm:p-5">
                                        <span class="font-hatton text-xl text-main block mb-2 sm:text-2xl"><?= htmlspecialchars($soin['libelle']) ?></span>
                                        <span class="font-hatton text-sm block mb-4"><?= htmlspecialchars($soin['description']) ?></span>
                                        <span class="font-hatton text-main"><?= htmlspecialchars($soin['duree']) ?> min • <?= htmlspecialchars(number_format($soin['prix'], 2, ',', '')) ?> €</span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-8 md:mb-10">
                    <div class="mb-6">
                        <p class="font-hatton text-xs uppercase tracking-[0.22em] sm:text-sm sm:tracking-[0.3em]">Étape 2</p>
                        <h2 class="font-hatton text-2xl text-main sm:text-3xl">Sélectionnez un expert</h2>
                    </div>

                    <?php if (empty($experts)): ?>
                        <div class="rounded-[22px] border border-div bg-white/50 p-4 sm:rounded-[28px] sm:p-5">
                            <p class="font-hatton text-main">
                                Aucun expert disponible pour le moment.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 md:gap-4">
                            <?php foreach ($experts as $expert): ?>
                                <?php $expertName = trim($expert['prenom'] . ' ' . $expert['nom']); ?>
                                <label class="block cursor-pointer">
                                    <input type="radio" name="expert" value="<?= htmlspecialchars($expert['id_user']) ?>"
                                        data-name="<?= htmlspecialchars($expertName) ?>" class="peer sr-only">
                                    <span
                                        class="block rounded-[22px] border border-div bg-white/50 p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-default peer-checked:shadow-xl/20 peer-checked:border-[#8F755E] sm:rounded-[28px] sm:p-5">
                                        <span class="font-hatton text-xl text-main block sm:text-2xl"><?= htmlspecialchars($expertName) ?></span>
                                        <span class="font-hatton text-sm block mt-2">Expert KAESKIN</span>
                                        <span class="inline-block mt-4 rounded-full bg-button px-4 py-2 font-hatton text-sm text-main sm:text-base">Disponible</span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-8 md:mb-10">
                    <div class="mb-6">
                        <p class="font-hatton text-xs uppercase tracking-[0.22em] sm:text-sm sm:tracking-[0.3em]">Étape 3</p>
                        <h2 class="font-hatton text-2xl text-main sm:text-3xl">Choisissez votre créneau</h2>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                        <div class="rounded-[24px] bg-div p-4 sm:rounded-[32px] sm:p-6">
                            <label for="date" class="block font-hatton text-main underline mb-3">Date souhaitée</label>
                            <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>"
                                class="w-full rounded-full bg-default px-4 py-3 font-hatton text-main focus:outline-none focus:ring-2 focus:ring-[#B09882]/50">
                            <p class="font-hatton text-sm mt-4 leading-relaxed">
                                Les disponibilités se mettent à jour selon le soin et l’expert choisis.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="slot-list">
                            <p class="col-span-full rounded-[20px] bg-default px-4 py-4 text-center font-hatton text-main">
                                Chargement des créneaux...
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="mb-6">
                        <p class="font-hatton text-xs uppercase tracking-[0.22em] sm:text-sm sm:tracking-[0.3em]">Validation</p>
                        <h2 class="font-hatton text-2xl text-main sm:text-3xl">
                            <?= $isConnected ? 'Confirmer votre rendez-vous' : 'Créer un compte pour continuer' ?>
                        </h2>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 md:gap-5 mb-6">
                        <label class="block cursor-pointer">
                            <input type="radio" name="payment_mode" value="Paiement en ligne" class="peer sr-only" checked>
                            <span
                                class="block rounded-[22px] border border-div bg-default p-4 transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E] sm:rounded-[28px] sm:p-5">
                                <span class="font-hatton text-xl text-main block mb-2 sm:text-2xl">Payer en ligne</span>
                                <span class="font-hatton block">Le rendez-vous sera ajouté au panier pour paiement.</span>
                            </span>
                        </label>

                        <label class="block cursor-pointer">
                            <input type="radio" name="payment_mode" value="Paiement sur place" class="peer sr-only">
                            <span
                                class="block rounded-[22px] border border-div bg-default p-4 transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E] sm:rounded-[28px] sm:p-5">
                                <span class="font-hatton text-xl text-main block mb-2 sm:text-2xl">Payer sur place</span>
                                <span class="font-hatton block">Le rendez-vous sera confirmé par mail sans passage panier.</span>
                            </span>
                        </label>
                    </div>

                    <div class="rounded-[24px] bg-div p-4 sm:rounded-[32px] sm:p-6 md:p-8">
                        <?php $alert = getAlert(); ?>
                        <?php if ($alert): ?>
                            <div class="mb-5 rounded-[22px] <?= $alert['type'] === 'success' ? 'bg-[#DDEEDC]' : 'bg-red-100' ?> px-4 py-3 text-center font-hatton <?= $alert['type'] === 'success' ? 'text-main' : 'text-red-700' ?> sm:rounded-full sm:px-5">
                                <?= htmlspecialchars($alert['message']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($isConnected): ?>
                            <p class="font-hatton text-main text-lg mb-3 sm:text-xl">
                                Vous êtes connecté avec <?= htmlspecialchars($_SESSION['email']) ?>.
                            </p>
                            <p class="font-hatton leading-relaxed mb-6">
                                Vous pouvez confirmer votre rendez-vous selon le mode de paiement choisi.
                            </p>
                            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                                <input type="hidden" name="duree" id="hidden-duration" value="">
                                <input type="hidden" name="prix" id="hidden-price" value="">
                                <button type="submit" <?= empty($experts) ? 'disabled' : '' ?>
                                    class="inline-flex w-full items-center justify-center rounded-full bg-button px-6 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 sm:w-auto sm:px-8">
                                    Confirmer le rendez-vous
                                
                                </button>
                                <span class="font-hatton" id="validation-note">
                                    Action suivante : ajout du rendez-vous au panier.
                                </span>
                            </div>
                        <?php else: ?>
                            <p class="font-hatton text-main text-lg mb-3 sm:text-xl">
                                Pour continuer, l’utilisateur doit d’abord créer un compte ou se connecter.
                            </p>
                            <p class="font-hatton leading-relaxed mb-6">
                                Toutes les sélections restent possibles avant cette étape, mais la réservation finale n’est
                                accessible qu’aux utilisateurs authentifiés.
                            </p>
                            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                                <a href="../auth/inscription.php"
                                    class="inline-flex w-full items-center justify-center rounded-full bg-button px-6 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 sm:w-auto sm:px-8">
                                    Créer un compte
                                </a>
                                <a href="../auth/login.php"
                                    class="inline-flex w-full items-center justify-center rounded-full border border-[#E8E2D9] px-6 py-4 font-hatton text-main transition-all duration-300 hover:bg-[#E8E2D9] sm:w-auto sm:px-8">
                                    Se connecter
                                </a>
                                <span class="font-hatton" id="validation-note">
                                    Action suivante : ajout du rendez-vous au panier.
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>


        </div>
    </section>
</main>

<script>
    const estConnecte = <?= $isConnected ? 'true' : 'false' ?>;
</script>
<script src="../assets/js/rdv.js?v=5"></script>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
