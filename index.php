<?php
include(__DIR__ . '/headers/header.php');
?>


<section>
    <div class="container mx-auto px-4 py-1">
        <div class="flex w-full justify-between bg-div rounded-[105px] shadow-xl/20 h-[500px] mx-auto mt-20 px-10 py-10">
            <div class=" justify-center items-center flex flex-col  mx-auto  px-10 py-10">
                <h1 class="text-5xl font-bold font-text mb-4 font-medium">Bienvenue ! On s’occupe de vous :
                </h1>
                <a href="<?= url('pages/rdv.php') ?>"
                    class="inline-block bg-button hover:!bg-[#E8E2D9] hover:!text-[#B09882] hover:scale-105 transition-all duration-300 font-bold py-3 px-8 rounded-full font-hatton font-medium shadow-xl/20 mt-6 text-main">
                    Prendre un rdv
                </a>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container mx-auto px-4 py-1 pb-16">
        <h1 class="text-4xl font-bold font-text mb-14 font-medium text-center mt-20">Nos services</h1>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">

            <div
                class="border border-div rounded-[18px] bg-[#F5F2ED] px-8 py-6 min-h-[280px] flex flex-col items-center text-center shadow-xl/20">
                <div class="w-20 h-20 rounded-2xl bg-button flex items-center justify-center mb-6">
                    <img src="<?= url('assets/images/services/scissors.svg') ?>" alt="Icône coiffure" class="w-11 h-11">
                </div>
                <h2 class="text-[2rem] font-text leading-none mb-3">Coiffure</h2>
                <a href="<?= url('pages/rdv.php') ?>"
                    class="mt-auto inline-flex items-center justify-center rounded-full bg-button px-10 py-3 font-hatton text-2xl text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">Réservez</a>
            </div>

            <div
                class="border border-div rounded-[18px] bg-[#F5F2ED] px-8 py-6 min-h-[280px] flex flex-col items-center text-center shadow-xl/20">
                <div class="w-20 h-20 rounded-2xl bg-button flex items-center justify-center mb-6">
                    <img src="<?= url('assets/images/services/star.svg') ?>" alt="Icône manucure" class="w-11 h-11">
                </div>
                <h2 class="text-[2rem] font-text leading-none mb-8">Manucure</h2>
                <a href="<?= url('pages/rdv.php') ?>"
                    class="mt-auto inline-flex items-center justify-center rounded-full bg-button px-10 py-3 font-hatton text-2xl text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">
                    Réservez
                </a>
            </div>

            <div
                class="border border-div rounded-[18px] bg-[#F5F2ED] px-8 py-6 min-h-[280px] flex flex-col items-center text-center shadow-xl/20">
                <div class="w-20 h-20 rounded-2xl bg-button flex items-center justify-center mb-6">
                    <img src="<?= url('assets/images/services/droplet.svg') ?>" alt="icone soin du visage" class="w-11 h-11">
                </div>
                <h2 class="text-[2rem] font-text leading-none mb-8">Soin du visage</h2>
                <a href="<?= url('pages/rdv.php') ?>"
                    class="mt-auto inline-flex items-center justify-center rounded-full bg-button px-10 py-3 font-hatton text-2xl text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">
                    Réservez
                </a>
            </div>

        </div>
    </div>
</section>


<section>

    <div class="container mx-auto px-4 py-1 pb-16">
        <div
            class="flex flex-1 w-full justify-between bg-div rounded-[105px] shadow-xl/20 h-[283px] mx-auto mt-20 px-10 py-10">
            <div class=" justify-center items-center flex flex-row flex-wrap gap-60 mx-auto px-10 py-10">
                <h1 class="text-4xl font-bold font-text font-medium">Rejoignez notre newsletter</h1>
                <form action="" class="flex flex-row items-center gap-8">
                    <input type="email" placeholder=" Entrez votre email"
                        class="bg-default rounded-[105px] border py-4 px-3 text-main font-hatton">
                    <button type="submit"
                        class="bg-button hover:!bg-[#E8E2D9] hover:!text-[#B09882] hover:scale-105 transition-all duration-300 text-white font-bold py-4 px-6 rounded-[105px]  font-hatton font-medium">
                        S'abonner
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>



<?php
include(__DIR__ . '/headers/footer.php');
?>
