<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

$errors = [];
$captchaImages = [
    'assets/images/captcha/ahmetyuksek-snow-covered-peaks-9771614_1920.jpg',
    'assets/images/captcha/alexandersix16-cenote-10225212_1920.jpg',
    'assets/images/captcha/ruslansikunov-chamomile-10065194_1920.jpg',
    'assets/images/captcha/studio_lichtfang-to-stage-9858926_1920.jpg',
    'assets/images/captcha/veronika_andrews-anna-10217636_1920.jpg',
];

if (!isset($_SESSION['captcha_x'], $_SESSION['captcha_y'], $_SESSION['captcha_image'])) {
    $_SESSION['captcha_x'] = random_int(90, 250);
    $_SESSION['captcha_y'] = random_int(45, 105);
    $_SESSION['captcha_image'] = $captchaImages[array_rand($captchaImages)];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';
    $newsletter = isset($_POST['newsletter']);
    $captchaPosition = (int) ($_POST['captcha_position'] ?? -100);

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

    if ($password !== $passwordConfirmation) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (abs($captchaPosition - $_SESSION['captcha_x']) > 10) {
        $errors[] = 'Le captcha est incorrect.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id_user FROM UTILISATEUR WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("INSERT INTO UTILISATEUR (nom, prenom, email, mot_de_passe, role, token_email) VALUES (?, ?, ?, ?, 'client', ?)");
        $stmt->execute([$nom, $prenom, $email, $hashedPassword, $token]);

        $idUser = $pdo->lastInsertId();

        if ($newsletter) {
            $stmtNewsletter = $pdo->prepare('INSERT INTO NEWSLETTER (email_inscrit, id_user) VALUES (?, ?)');
            $stmtNewsletter->execute([$email, $idUser]);
        }

        $protocole = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $lien = $protocole . '://' . $_SERVER['HTTP_HOST'] . '/PA/auth/verifier_email.php?token=' . $token;
        $message = "Bonjour $prenom,\n\nCliquez sur ce lien pour confirmer votre compte KAESKIN :\n$lien";

        sendMail($email, 'Confirmation de votre compte KAESKIN', $message);

        redirect('login.php', 'success', 'Compte créé. Un email de confirmation vient de vous être envoyé.');
    }
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="mx-auto max-w-3xl">
            <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] shadow-lg px-6 py-10 md:px-10 md:py-12">
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
                                value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" placeholder="Votre prénom"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                required autocomplete="given-name" />
                        </div>

                        <div>
                            <label for="nom" class="mb-2 block font-hatton text-xl text-main">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                                placeholder="Votre nom"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                required autocomplete="family-name" />
                        </div>

                        <div>
                            <label for="email" class="mb-2 block font-hatton text-xl text-main">Email</label>
                            <input type="email" id="email" name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="votre@email.com"
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
                            <label for="password_confirmation"
                                class="mb-2 block font-hatton text-xl text-main">Confirmation</label>
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

                        <div>
                            <p class="mb-2 block font-hatton text-xl text-main">Captcha</p>
                            <div class="relative h-[180px] overflow-hidden rounded-[24px] bg-cover bg-center"
                                style="background-image: url('<?= url($_SESSION['captcha_image']) ?>');">
                                <div class="absolute h-11 w-11 rounded-[10px] border-2 border-white/70 bg-black/35"
                                    style="left: <?= (int) $_SESSION['captcha_x'] ?>px; top: <?= (int) $_SESSION['captcha_y'] ?>px;">
                                </div>
                                <div id="captcha-piece"
                                    class="absolute left-0 h-11 w-11 rounded-[10px] bg-cover shadow-lg transition-shadow"
                                    style="
                                        top: <?= (int) $_SESSION['captcha_y'] ?>px;
                                        background-image: url('<?= url($_SESSION['captcha_image']) ?>');
                                        background-size: 416px 180px;
                                        background-position: -<?= (int) $_SESSION['captcha_x'] ?>px -<?= (int) $_SESSION['captcha_y'] ?>px;
                                    "></div>
                            </div>
                            <input type="range" id="captcha-range" class="mt-4 w-full cursor-pointer accent-[#B09882]"
                                min="0" max="300" value="0">
                            <input type="hidden" id="captcha-position" name="captcha_position" value="0">
                            <p class="mt-2 font-hatton text-sm text-main">
                                Faites glisser la pièce au bon endroit.
                            </p>
                        </div>

                        <button type="submit"
                            class="mt-4 w-full rounded-full bg-button px-6 py-4 font-hatton text-xl text-main shadow-md transition-all duration-300 hover:scale-[1.01] hover:bg-[#D4C0AB]">
                            S’inscrire
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="<?= url('assets/js/captcha.js') ?>"></script>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
