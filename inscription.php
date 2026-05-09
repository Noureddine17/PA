<?php
session_start();
require_once 'config/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirmation'];
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;

    if (empty($prenom)) {
        $errors[] = 'Le prénom est requis.';
    }
    if (empty($nom)) {
        $errors[] = 'Le nom est requis.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id_user FROM UTILISATEUR WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO UTILISATEUR (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, 'user')");
        $stmt->execute([$nom, $prenom, $email, $hash]);

        $id_user = $pdo->lastInsertId();

        if ($newsletter) {
            $stmt2 = $pdo->prepare("INSERT INTO NEWSLETTER (email_inscrit, date_inscription) VALUES (?, NOW())");
            $stmt2->execute([$email]);
        }

        $_SESSION['user_id'] = $id_user;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $prenom . ' ' . $nom;

        header('Location: /PA/index.php');
        exit;
    }
}

include('headers/header.php');
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
                                class="px-6 py-3 text-center font-hatton text-main transition-colors hover:bg-[#EFE6DB]">
                                Connexion
                            </a>
                            <a href="inscription.php"
                                class="bg-div px-6 py-3 text-center font-hatton text-[#F7F3EE] transition-colors">
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

                    <form action="inscription.php" method="post" class="space-y-5">
                        <div>
                            <label for="prenom" class="mb-2 block font-hatton text-xl text-main">Prénom</label>
                            <input type="text" id="prenom" name="prenom"
                                value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                                placeholder="Votre prénom"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                required autocomplete="given-name" />
                        </div>

                        <div>
                            <label for="nom" class="mb-2 block font-hatton text-xl text-main">Nom</label>
                            <input type="text" id="nom" name="nom"
                                value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                                placeholder="Votre nom"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                required autocomplete="family-name" />
                        </div>

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
                                required autocomplete="new-password" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block font-hatton text-xl text-main">Confirmation</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                placeholder="••••••••"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                required autocomplete="new-password" />
                        </div>

                        <div class="flex items-center gap-3 px-1">
                            <input type="checkbox" id="newsletter" name="newsletter"
                                class="h-5 w-5 rounded border-[#D4C0AB] accent-[#B09882] cursor-pointer"
                                <?= isset($_POST['newsletter']) ? 'checked' : '' ?> />
                            <label for="newsletter" class="font-hatton text-main cursor-pointer">
                                S'abonner à la newsletter
                            </label>
                        </div>

                        <button type="submit"
                            class="mt-4 w-full rounded-full bg-button px-6 py-4 font-hatton text-xl text-main shadow-[0_10px_20px_rgba(120,94,65,0.12)] transition-all duration-300 hover:scale-[1.01] hover:bg-[#D4C0AB]">
                            S'inscrire
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include('headers/footer.php'); ?>