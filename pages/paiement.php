<?php
include(__DIR__ . '/../headers/header.php');
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-12">
        <div class="bg-div rounded-[48px] md:rounded-[72px] shadow-xl/20 px-6 py-10 md:px-12 md:py-14">
            <div class="max-w-3xl">
                <p class="font-hatton text-sm uppercase tracking-[0.35em] mb-4">Paiement</p>
                <h1 class="font-hatton text-main text-4xl md:text-5xl leading-tight mb-5">
                    Étape de paiement à connecter.
                </h1>
                <p class="text-main font-hatton text-lg leading-relaxed mb-8">
                    La page panier redirige désormais vers cette étape dédiée. Il restera à brancher ici votre
                    formulaire de règlement ou votre prestataire de paiement.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="panier.php"
                        class="inline-flex items-center justify-center rounded-full bg-button px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">
                        Retour au panier
                    </a>
                    <a href="shop.php"
                        class="inline-flex items-center justify-center rounded-full border border-div bg-[#F5F2ED] px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:bg-[#E8E2D9]">
                        Continuer mes achats
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
