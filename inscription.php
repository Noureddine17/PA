<?php
include('headers/header.php');
?>

<div class="container mx-auto px-8 py-1">
    <div class=" flex justify-center items-center my-12 py-12">
        <div class="w-full max-w-3xl">
            <div class="flex justify-center">
                <div class="w-full max-w-3xl">

                    <div class="bg-div rounded-[105px] p-16 shadow-xl/30">

                        <h1 class="text-4xl font-bold font-text mb-4 font-hatton font-medium text-center">Créer un
                            compte
                        </h1>
                        <!-- <p class="text-main text-center mb-6 font-hatton font-medium">Rejoignez-nous pour une expérience
                        unique.</p> -->

                        <form action="inscription.php" method="post">

                            <div class="mb-4">
                                <label for="name"
                                    class="block text-main font-hatton font-medium mb-2 underline-offset-4 underline">Nom
                                    complet : </label>
                                <input type="text" id="name" name="name" placeholder="ex: Moktar Benmoktar"
                                    class="bg-[#E8E2D9] rounded-full shadow-xl border-0 py-3 px-4 w-full text-[#4D443E] font-hatton placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#B09882]/50"
                                    required autocomplete="name" />
                            </div>

                            <div class="mb-4">
                                <label for="email"
                                    class="block text-main font-hatton font-medium mb-2 underline-offset-4 underline">Email
                                    : </label>
                                <input type="email" id="email" name="email" placeholder="ex: moktar@mail.com"
                                    class="bg-[#E8E2D9] rounded-full shadow-xl border-0 py-3 px-4 w-full text-[#4D443E] font-hatton placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#B09882]/50"
                                    required autocomplete="email" />
                            </div>

                            <div class="mb-6">
                                <label for="password"
                                    class="block text-main font-hatton font-medium mb-2 underline-offset-4 underline">Mot
                                    de
                                    passe : </label>
                                <input type="password" id="password" name="password" placeholder="••••••••"
                                    class="bg-[#E8E2D9] rounded-full shadow-xl border-0 py-3 px-4 w-full text-[#4D443E] font-hatton placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#B09882]/50"
                                    required autocomplete="new-password" />
                            </div>

                            <div class="mb-6">
                                <label for="password_confirmation"
                                    class="block text-main font-hatton font-medium mb-2 underline-offset-4 underline">Confirmer
                                    le mot de
                                    passe : </label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    placeholder="••••••••"
                                    class="bg-[#E8E2D9] rounded-full shadow-xl border-0 py-3 px-4 w-full text-[#4D443E] font-hatton placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#B09882]/50"
                                    required autocomplete="new-password" />
                            </div>

                            <button type="submit"
                                class="w-full bg-button hover:!bg-[#E8E2D9] hover:!text-[#B09882] hover:scale-105 active:scale-95 transition-all duration-300 text-[#4D443E] font-bold py-3 px-6 rounded-full shadow-xl/20 font-hatton font-medium text-lg">
                                S'inscrire
                            </button>

                            <div class="text-center mt-6">
                                <span class="text-main">Déjà un compte ?</span>
                                <a class="font-hatton font-medium text-main hover:!text-[#C5B49E] hover:transition-colors transition-colors inline-block active:scale-95"
                                    href="login.php">
                                    Se connecter
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>


<?php
include('headers/footer.php');
?>