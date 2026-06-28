<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

$token = $_GET['token'] ?? '';
$errors = [];
$user = null;
$showForm = false;

if (empty($token)) {
    $errors[] = 'Token de réinitialisation manquant ou invalide.';
} else {
    try {
        $stmt = $pdo->prepare('SELECT * FROM UTILISATEUR WHERE reset_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $errors[] = 'Ce token est invalide ou a déjà été utilisé.';
        } else {
            $expiryDate = new DateTime($user['reset_token_expires_at']);
            $now = new DateTime();
            if ($now > $expiryDate) {
                $errors[] = 'Ce token a expiré. Veuillez refaire une demande.';
            } else {
                $showForm = true;
            }
        }
    } catch (PDOException $e) {
        error_log('Erreur de vérification du token: ' . $e->getMessage());
        $errors[] = 'Une erreur de base de données est survenue. Il est possible que les colonnes `reset_token` et `reset_token_expires_at` manquent dans la table UTILISATEUR.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $errors[] = 'Veuillez remplir les deux champs de mot de passe.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit faire au moins 8 caractères.';
    } elseif ($password !== $password_confirm) {
        $errors[] = 'Les mots de passe ne correspondent pas.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('UPDATE UTILISATEUR SET mot_de_passe = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id_user = ?');
        if ($stmt->execute([$hashedPassword, $user['id_user']])) {
            redirect('login.php', 'success', 'Mot de passe réinitialisé. Vous pouvez vous connecter.');
        } else {
            $errors[] = 'Erreur lors de la mise à jour du mot de passe.';
        }
    }
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto max-w-3xl">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 shadow-[0_18px_40px_rgba(120,94,65,0.12)] md:px-10 md:py-12">
            <div class="mx-auto max-w-[26rem]">
                <header class="text-center mb-8">
                    <h1 class="font-hatton text-main text-4xl md:text-5xl leading-none mb-4">Réinitialiser le mot de passe</h1>
                </header>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 rounded-[20px] border border-red-200 bg-red-50 px-5 py-4">
                        <?php foreach ($errors as $error): ?>
                            <p class="font-hatton text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($showForm): ?>
                    <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="post" class="space-y-5">
                        <div>
                            <label for="password" class="mb-2 block font-hatton text-xl text-main">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40" required />
                        </div>
                        <div>
                            <label for="password_confirm" class="mb-2 block font-hatton text-xl text-main">Confirmer le mot de passe</label>
                            <input type="password" id="password_confirm" name="password_confirm" placeholder="••••••••" class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40" required />
                        </div>
                        <button type="submit" class="mt-4 w-full rounded-full bg-button px-6 py-4 font-hatton text-xl text-main shadow-[0_10px_20px_rgba(120,94,65,0.12)] transition-all duration-300 hover:scale-[1.01] hover:bg-[#D4C0AB]">Enregistrer</button>
                    </form>
                <?php else: ?>
                    <div class="text-center"><a href="forgot_password.php" class="font-hatton text-main underline">Faire une nouvelle demande</a></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include(__DIR__ . '/../headers/footer.php'); ?>