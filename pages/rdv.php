<?php
include(__DIR__ . '/../headers/header.php');

$isConnected = isset($_SESSION['id_user']);
?>

<main class="pb-20">
   

    <section class="container mx-auto px-4 pt-12">
        <div class="grid gap-8 xl:grid-cols-[1.35fr_0.65fr]">
            <form id="rdv-form" class="bg-[#F5F2ED] border border-div rounded-[40px] md:rounded-[56px] p-6 md:p-10 shadow-xl/20">
                <div class="mb-10">
                    <div class="flex items-center justify-between gap-4 mb-6">
                        <div>
                            <p class="font-hatton text-sm uppercase tracking-[0.3em]">Étape 1</p>
                            <h2 class="font-hatton text-3xl text-main">Choisissez votre soin</h2>
                        </div>
                        <span class="rounded-full bg-button px-5 py-2 font-hatton text-main">Soins disponibles</span>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="block cursor-pointer">
                            <input type="radio" name="service" value="Soin du visage signature" class="peer sr-only"
                                data-duration="60 min" data-price="85 €" checked>
                            <span
                                class="block rounded-[28px] border border-div bg-default p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block mb-2">Soin du visage</span>
                                <span class="font-hatton text-sm block mb-4">Nettoyage profond et éclat immédiat</span>
                                <span class="font-hatton text-main">60 min • 85 €</span>
                            </span>
                        </label>
                                            
                        <label class="block cursor-pointer">
                            <input type="radio" name="service" value="Rituel anti-stress" class="peer sr-only"
                                data-duration="75 min" data-price="110 €">
                            <span
                                class="block rounded-[28px] border border-div bg-default p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block mb-2">Rituel relaxant</span>
                                <span class="font-hatton text-sm block mb-4">Massage visage et détente profonde</span>
                                <span class="font-hatton text-main">75 min • 110 €</span>
                            </span>
                        </label>

                        <label class="block cursor-pointer">
                            <input type="radio" name="service" value="Hydratation intense" class="peer sr-only"
                                data-duration="50 min" data-price="78 €">
                            <span
                                class="block rounded-[28px] border border-div bg-default p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block mb-2">Hydratation intense</span>
                                <span class="font-hatton text-sm block mb-4">Soin nourrissant pour peau fatiguée</span>
                                <span class="font-hatton text-main">50 min • 78 €</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mb-10">
                    <div class="mb-6">
                        <p class="font-hatton text-sm uppercase tracking-[0.3em]">Étape 2</p>
                        <h2 class="font-hatton text-3xl text-main">Sélectionnez un expert</h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="block cursor-pointer">
                            <input type="radio" name="expert" value="Lina" class="peer sr-only" checked>
                            <span
                                class="block rounded-[28px] border border-div bg-white/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-default peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block">Lina</span>
                                <span class="font-hatton text-sm block mt-2">Experte glow & soin signature</span>
                                <span class="inline-block mt-4 rounded-full bg-button px-4 py-2 font-hatton text-main">4.9 / 5</span>
                            </span>
                        </label>

                        <label class="block cursor-pointer">
                            <input type="radio" name="expert" value="Camille" class="peer sr-only">
                            <span
                                class="block rounded-[28px] border border-div bg-white/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-default peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block">Camille</span>
                                <span class="font-hatton text-sm block mt-2">Spécialiste relaxation & rituel sensoriel</span>
                                <span class="inline-block mt-4 rounded-full bg-button px-4 py-2 font-hatton text-main">4.8 / 5</span>
                            </span>
                        </label>

                        <label class="block cursor-pointer">
                            <input type="radio" name="expert" value="Sarah" class="peer sr-only">
                            <span
                                class="block rounded-[28px] border border-div bg-white/50 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20 peer-checked:bg-default peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block">Sarah</span>
                                <span class="font-hatton text-sm block mt-2">Référence peaux sensibles & hydratation</span>
                                <span class="inline-block mt-4 rounded-full bg-button px-4 py-2 font-hatton text-main">5.0 / 5</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mb-10">
                    <div class="mb-6">
                        <p class="font-hatton text-sm uppercase tracking-[0.3em]">Étape 3</p>
                        <h2 class="font-hatton text-3xl text-main">Choisissez votre créneau</h2>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                        <div class="rounded-[32px] bg-div p-6">
                            <label for="date" class="block font-hatton text-main underline mb-3">Date souhaitée</label>
                            <input type="date" id="date" name="date" min="<?= date('Y-m-d') ?>"
                                class="w-full rounded-full bg-default px-4 py-3 font-hatton text-main focus:outline-none focus:ring-2 focus:ring-[#B09882]/50">
                            <p class="font-hatton text-sm mt-4 leading-relaxed">
                                Les disponibilités se mettent à jour selon le soin et l’expert choisis.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="slot" value="09:00" class="peer sr-only" checked>
                                <span class="block rounded-[24px] border border-div bg-default px-4 py-4 text-center font-hatton text-main transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:border-[#8F755E]">09:00</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="slot" value="10:30" class="peer sr-only">
                                <span class="block rounded-[24px] border border-div bg-default px-4 py-4 text-center font-hatton text-main transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:border-[#8F755E]">10:30</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="slot" value="12:00" class="peer sr-only">
                                <span class="block rounded-[24px] border border-div bg-default px-4 py-4 text-center font-hatton text-main transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:border-[#8F755E]">12:00</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="slot" value="14:00" class="peer sr-only">
                                <span class="block rounded-[24px] border border-div bg-default px-4 py-4 text-center font-hatton text-main transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:border-[#8F755E]">14:00</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="slot" value="15:30" class="peer sr-only">
                                <span class="block rounded-[24px] border border-div bg-default px-4 py-4 text-center font-hatton text-main transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:border-[#8F755E]">15:30</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="slot" value="17:00" class="peer sr-only">
                                <span class="block rounded-[24px] border border-div bg-default px-4 py-4 text-center font-hatton text-main transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:border-[#8F755E]">17:00</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="mb-6">
                        <p class="font-hatton text-sm uppercase tracking-[0.3em]">Validation</p>
                        <h2 class="font-hatton text-3xl text-main">
                            <?= $isConnected ? 'Confirmer votre rendez-vous' : 'Créer un compte pour continuer' ?>
                        </h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 mb-6">
                        <label class="block cursor-pointer">
                            <input type="radio" name="payment_mode" value="Paiement en ligne" class="peer sr-only" checked>
                            <span
                                class="block rounded-[28px] border border-div bg-default p-5 transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block mb-2">Payer en ligne</span>
                                <span class="font-hatton block">Le rendez-vous sera ajouté au panier pour paiement.</span>
                            </span>
                        </label>

                        <label class="block cursor-pointer">
                            <input type="radio" name="payment_mode" value="Paiement sur place" class="peer sr-only">
                            <span
                                class="block rounded-[28px] border border-div bg-default p-5 transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:shadow-xl/20 peer-checked:border-[#8F755E]">
                                <span class="font-hatton text-2xl text-main block mb-2">Payer sur place</span>
                                <span class="font-hatton block">Le rendez-vous sera confirmé par mail sans passage panier.</span>
                            </span>
                        </label>
                    </div>

                    <div class="rounded-[32px] bg-div p-6 md:p-8">
                        <div id="rdv-message" class="hidden mb-5 rounded-full bg-[#DDEEDC] px-5 py-3 text-center font-hatton text-main"></div>

                        <?php if ($isConnected): ?>
                            <p class="font-hatton text-main text-xl mb-3">
                                Vous êtes connecté avec <?= htmlspecialchars($_SESSION['email']) ?>.
                            </p>
                            <p class="font-hatton leading-relaxed mb-6">
                                Vous pouvez confirmer votre rendez-vous selon le mode de paiement choisi.
                            </p>
                            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-full bg-button px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105">
                                    Confirmer le rendez-vous
                                </button>
                                <span class="font-hatton" id="validation-note">
                                    Action suivante : ajout du rendez-vous au panier.
                                </span>
                            </div>
                        <?php else: ?>
                            <p class="font-hatton text-main text-xl mb-3">
                                Pour continuer, l’utilisateur doit d’abord créer un compte ou se connecter.
                            </p>
                            <p class="font-hatton leading-relaxed mb-6">
                                Toutes les sélections restent possibles avant cette étape, mais la réservation finale n’est
                                accessible qu’aux utilisateurs authentifiés.
                            </p>
                            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                                <a href="../auth/inscription.php"
                                    class="inline-flex items-center justify-center rounded-full bg-button px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105">
                                    Créer un compte
                                </a>
                                <a href="../auth/login.php"
                                    class="inline-flex items-center justify-center rounded-full border border-[#E8E2D9] px-8 py-4 font-hatton text-main transition-all duration-300 hover:bg-[#E8E2D9]">
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

            <aside class="sticky top-6 self-start space-y-6">
                <div class="bg-div rounded-[40px] p-6 md:p-8 shadow-xl/20">
                    <p class="font-hatton text-sm uppercase tracking-[0.3em] mb-4">Résumé</p>
                    <h2 class="font-hatton text-3xl text-main mb-6">Votre sélection</h2>

                    <div class="space-y-4">
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-sm mb-1">Soin</p>
                            <p class="font-hatton text-2xl text-main" id="summary-service">Soin du visage signature</p>
                        </div>
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-sm mb-1">Expert</p>
                            <p class="font-hatton text-2xl text-main" id="summary-expert">Lina</p>
                        </div>
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-sm mb-1">Date & créneau</p>
                            <p class="font-hatton text-2xl text-main" id="summary-slot">09:00</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[28px] border border-[#E8E2D9] p-5">
                        <div class="flex items-center justify-between">
                            <span class="font-hatton text-main">Durée</span>
                            <span class="font-hatton text-main" id="summary-duration">60 min</span>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span class="font-hatton text-main">Tarif</span>
                            <span class="font-hatton text-main" id="summary-price">85 €</span>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span class="font-hatton text-main">Mode</span>
                            <span class="font-hatton text-main" id="summary-payment">Paiement en ligne</span>
                        </div>
                    </div>
                </div>

            </aside>
        </div>
    </section>
</main>

<script>
    const estConnecte = <?= $isConnected ? 'true' : 'false' ?>;
</script>
<script src="../assets/js/rdv.js"></script>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
