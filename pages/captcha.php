<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

requireRole($pdo, 'admin', '../auth/login.php', '../dashboards/client.php', 'Accès réservé aux administrateurs.');

$dossierCaptcha = __DIR__ . '/../assets/images/captcha/';
$extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image = $_FILES['captcha_image'] ?? null;

    if (!$image || $image['error'] !== UPLOAD_ERR_OK) {
        redirect('captcha.php', 'error', 'Veuillez choisir une image.');
    }

    $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $extensionsAutorisees, true)) {
        redirect('captcha.php', 'error', 'Format refusé. Utilisez JPG, PNG ou WEBP.');
    }

    $typeImage = mime_content_type($image['tmp_name']);
    $typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($typeImage, $typesAutorises, true)) {
        redirect('captcha.php', 'error', 'Le fichier envoyé doit être une vraie image.');
    }

    if (!is_dir($dossierCaptcha)) {
        mkdir($dossierCaptcha, 0755, true);
    }

    $nomFichier = 'captcha-' . date('Ymd-His') . '-' . random_int(1000, 9999) . '.' . $extension;
    $cheminFinal = $dossierCaptcha . $nomFichier;

    if (!move_uploaded_file($image['tmp_name'], $cheminFinal)) {
        redirect('captcha.php', 'error', 'Impossible d’enregistrer l’image.');
    }

    redirect('captcha.php', 'success', 'Image ajoutée au captcha.');
}

$images = glob($dossierCaptcha . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto max-w-3xl">
        <div class="rounded-[32px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Admin</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Images du captcha</h1>

            <form action="captcha.php" method="post" enctype="multipart/form-data" class="mt-8 space-y-5">
                <div>
                    <label for="captcha_image" class="font-hatton text-main">Ajouter une image</label>
                    <input type="file" id="captcha_image" name="captcha_image" accept=".jpg,.jpeg,.png,.webp"
                        class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main" required>
                </div>

                <button type="submit" class="rounded-full bg-button px-6 py-3 font-hatton text-main">
                    Enregistrer l'image
                </button>

                <a href="../dashboards/admin.php" class="ml-3 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                    Retour
                </a>
            </form>
        </div>

        <div class="mt-8 rounded-[32px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
            <h2 class="font-hatton text-3xl text-main">Images disponibles</h2>

            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3">
                <?php foreach ($images as $image): ?>
                    <?php $nomImage = basename($image); ?>
                    <img src="<?= url('assets/images/captcha/' . $nomImage) ?>" alt="Image captcha"
                        class="h-32 w-full rounded-[20px] border border-[#CBB59D] object-cover">
                <?php endforeach; ?>

                <?php if (empty($images)): ?>
                    <p class="col-span-full font-hatton text-main">Aucune image pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
