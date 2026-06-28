<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

requireRole($pdo, 'admin', '../auth/login.php', 'client.php', 'Accès réservé aux administrateurs.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($sujet) || empty($message)) {
        redirect('send_newsletter.php', 'error', 'Le sujet et le message ne peuvent pas être vides.');
    }

    $stmt = $pdo->query('SELECT email_inscrit FROM NEWSLETTER');
    $subscribers = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($subscribers)) {
        redirect('send_newsletter.php', 'success', 'Aucun abonné à la newsletter à qui envoyer des emails.');
    }

    $sentCount = 0;
    foreach ($subscribers as $email) {
        if (sendMail($email, $sujet, $message)) {
            $sentCount++;
        }
    }

    redirect('admin.php', 'success', "$sentCount e-mail(s) de newsletter envoyé(s) avec succès.");
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto max-w-3xl">
        <div class="rounded-[32px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Admin</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Envoyer une Newsletter</h1>

            <?php displayAlert(); ?>

            <form action="send_newsletter.php" method="post" class="mt-8 space-y-5">
                <div>
                    <label for="sujet" class="font-hatton text-main">Sujet de l'e-mail</label>
                    <input type="text" id="sujet" name="sujet" class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main" required>
                </div>
                <div>
                    <label for="message" class="font-hatton text-main">Message</label>
                    <textarea id="message" name="message" rows="10" class="mt-2 w-full rounded-[22px] border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main" required></textarea>
                </div>
                <button type="submit" class="rounded-full bg-button px-6 py-3 font-hatton text-main">Envoyer à tous les abonnés</button>
                <a href="admin.php" class="ml-3 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">Retour</a>
            </form>
        </div>
    </section>
</main>

<?php include(__DIR__ . '/../headers/footer.php'); ?>
