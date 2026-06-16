<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

$data = json_decode(file_get_contents('php://input'), true);


$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    }

    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT * FROM UTILISATEUR WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
            $errors[] = 'Email ou mot de passe incorrect.';
        } elseif (!$user['verif_email']) {
            $errors[] = 'Vous devez confirmer votre email avant de vous connecter.';
        } else {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            modiflog(true,$email);
            $_SESSION['last_activity'] = time();

            redirect('../index.php', 'success', 'Connexion réussie.');
        }
    }
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="mx-auto max-w-3xl">
            <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 shadow-[0_18px_40px_rgba(120,94,65,0.12)] md:px-10 md:py-12">
                <div class="mx-auto max-w-[26rem]">
                    <header class="text-center mb-8">
                        <h1 class="font-hatton text-main text-5xl md:text-6xl leading-none mb-4">KAESKIN</h1>
                        <p class="font-hatton text-accent text-lg">Votre espace beauté</p>
                    </header>

                    <div class="mb-8 rounded-full border border-[#CBB59D] overflow-hidden bg-white/60">
                        <div class="grid grid-cols-2">
                            <a href="login.php"
                                class="bg-div px-6 py-3 text-center font-hatton text-[#F7F3EE] transition-colors">
                                Connexion
                            </a>
                            <a href="inscription.php"
                                class="px-6 py-3 text-center font-hatton text-main transition-colors hover:bg-[#EFE6DB]">
                                Inscription
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="mb-6 rounded-[20px] border border-red-200 bg-red-50 px-5 py-4">
                            <?php foreach ($errors as $error): ?>
                                <p class="font-hatton text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="post" class="space-y-5">
                        <div>
                            <label for="email" class="mb-2 block font-hatton text-xl text-main">Email</label>
                            <input type="email" id="email" name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                placeholder="votre@email.com"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                required autocomplete="email" />
                        </div>

                        <div>
                            <label for="password" class="mb-2 block font-hatton text-xl text-main">Mot de passe</label>
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                required autocomplete="current-password" />
                        </div>

                        <div class="flex justify-end">
                            <a href="#"
                                class="font-hatton text-sm text-[#B7A28D] transition-colors">
                                Mot de passe oublié ?
                            </a>
                        </div>

                        <button type="submit"
                            class="mt-4 w-full rounded-full bg-button px-6 py-4 font-hatton text-xl text-main shadow-[0_10px_20px_rgba(120,94,65,0.12)] transition-all duration-300 hover:scale-[1.01] hover:bg-[#D4C0AB]">
                            Se connecter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>


<?php
include(__DIR__ . '/../headers/footer.php');
?>
