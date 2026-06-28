<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

$token = $_GET['token'] ?? '';
$errors = [];
$message = '';
$isValid = false;

if (!empty($token)) {
    $stmt = $pdo->prepare('SELECT id_user, email FROM UTILISATEUR WHERE token_reset = ? AND token_reset_expires > NOW()');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $isValid = true;
    } else {
        $message = 'Lien de réinitialisation invalide ou expiré.';
    }
} else {
    $message = 'Lien de réinitialisation invalide.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid && !empty($token)) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword)) {
        $errors[] = 'Le mot de passe est requis.';
    } elseif (strlen($newPassword) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($newPassword !== $confirmPassword) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE UTILISATEUR SET mot_de_passe = ?, token_reset = NULL, token_reset_expires = NULL WHERE id_user = ?');
        $stmt->execute([$hashedPassword, $user['id_user']]);

        redirect('login.php', 'success', 'Votre mot de passe a été réinitialisé. Vous pouvez maintenant vous connecter.');
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
                        <h1 class="font-hatton text-main text-4xl md:text-5xl leading-none mb-4">Réinitialiser le mot de passe</h1>
                    </header>

                    <?php if ($message): ?>
                        <div class="mb-6 rounded-[20px] border border-red-200 bg-red-50 px-5 py-4">
                            <p class="font-hatton text-red-700 text-sm"><?= htmlspecialchars($message) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($isValid && empty($message)): ?>
                        <?php if (!empty($errors)): ?>
                            <div class="mb-6 rounded-[20px] border border-red-200 bg-red-50 px-5 py-4">
                                <?php foreach ($errors as $error): ?>
                                    <p class="font-hatton text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form action="reset_password.php?token=<?= urlencode($token) ?>" method="post" class="space-y-5">
                            <div>
                                <label for="password" class="mb-2 block font-hatton text-xl text-main">Nouveau mot de passe</label>
                                <input type="password" id="password" name="password" placeholder="••••••••"
                                    class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                    required autocomplete="new-password" />
                            </div>

                            <div>
                                <label for="confirm_password" class="mb-2 block font-hatton text-xl text-main">Confirmer le mot de passe</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••"
                                    class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                    required autocomplete="new-password" />
                            </div>

                            <button type="submit"
                                class="mt-4 w-full rounded-full bg-button px-6 py-4 font-hatton text-xl text-main shadow-[0_10px_20px_rgba(120,94,65,0.12)] transition-all duration-300 hover:scale-[1.01] hover:bg-[#D4C0AB]">
                                Réinitialiser le mot de passe
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="mt-6 text-center">
                        <a href="login.php" class="font-hatton text-sm text-[#B7A28D] transition-colors hover:text-main">
                            Retour à la connexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
