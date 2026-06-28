<?php
session_start();
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../config/functions.php');

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email invalide.';
    } else {
        $stmt = $pdo->prepare('SELECT id_user, prenom FROM UTILISATEUR WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);
            $stmt = $pdo->prepare('UPDATE UTILISATEUR SET reset_token = ?, reset_token_expires_at = ? WHERE id_user = ?');
            $stmt->execute([$token, $expiresAt, $user['id_user']]);

            $resetLink = 'https://' . $_SERVER['HTTP_HOST'] . '/PA/auth/reset_password.php?token=' . $token;

            $subject = 'Réinitialiser votre mot de passe KAESKIN';
            $messageBody = "Bonjour " . htmlspecialchars($user['prenom']) . ",\n\n";
            $messageBody .= "Vous avez demandé une réinitialisation de mot de passe.\n";
            $messageBody .= "Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe :\n\n";
            $messageBody .= $resetLink . "\n\n";
            $messageBody .= "Ce lien expire dans 1 heure.\n\n";
            $messageBody .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
            $messageBody .= "Cordialement,\nL'équipe KAESKIN";

            sendMail($email, $subject, $messageBody);
            $success = true;
            $message = 'Un email de réinitialisation a été envoyé à votre adresse.';
        } else {
            $success = true;
            $message = 'Si cet email existe, un lien de réinitialisation a été envoyé.';
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
                        <h1 class="font-hatton text-main text-4xl md:text-5xl leading-none mb-4">Mot de passe oublié</h1>
                        <p class="font-hatton text-accent text-lg">Nous vous aiderons à le récupérer</p>
                    </header>

                    <?php if ($message): ?>
                        <div class="mb-6 rounded-[20px] border <?= $success ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' ?> px-5 py-4">
                            <p class="font-hatton <?= $success ? 'text-green-700' : 'text-red-700' ?> text-sm"><?= htmlspecialchars($message) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!$success || $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
                        <form action="forgot_password.php" method="post" class="space-y-5">
                            <div>
                                <label for="email" class="mb-2 block font-hatton text-xl text-main">Email</label>
                                <input type="email" id="email" name="email"
                                    placeholder="votre@email.com"
                                    class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-4 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40"
                                    required autocomplete="email" />
                            </div>

                            <button type="submit"
                                class="mt-4 w-full rounded-full bg-button px-6 py-4 font-hatton text-xl text-main shadow-[0_10px_20px_rgba(120,94,65,0.12)] transition-all duration-300 hover:scale-[1.01] hover:bg-[#D4C0AB]">
                                Envoyer le lien
                            </button>
                        </form>

                        <div class="mt-6 text-center">
                            <a href="login.php" class="font-hatton text-sm text-[#B7A28D] transition-colors hover:text-main">
                                Retour à la connexion
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
