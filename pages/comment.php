<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

$blogPosts = [
    ['slug' => '5-etapes-peau-radieuse', 'title' => '5 Étapes pour une Peau Radieuse'],
    ['slug' => 'science-de-nos-serums', 'title' => 'La Science de nos Sérums'],
    ['slug' => 'routine-matin-vs-soir', 'title' => 'Routine Matin vs Soir'],
];

$slug = $_GET['article'] ?? '';
if (empty($slug)) {
    redirect('blog.php');
}

$selected = null;
foreach ($blogPosts as $p) {
    if ($p['slug'] === $slug) {
        $selected = $p;
        break;
    }
}

if (!$selected) {
    redirect('blog.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['id_user'])) {
        redirect('../auth/login.php', 'error', 'Vous devez vous connecter pour commenter.');
    }

    $comment = trim($_POST['comment'] ?? '');
    if ($comment === '') {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Le commentaire ne peut pas être vide.'];
        redirect('comment.php?article=' . urlencode($slug));
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO BLOG_COMMENT (article_slug, id_user, content) VALUES (?, ?, ?)');
        $stmt->execute([$slug, $_SESSION['id_user'], $comment]);
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Commentaire ajouté.'];
    } catch (Exception $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Erreur lors de l\'enregistrement du commentaire.'];
    }

    redirect('blog.php?article=' . urlencode($slug) . '#post-' . urlencode($slug));
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-10 md:pt-16">
        <div class="mx-auto max-w-3xl">
            <header class="mb-10 text-center md:mb-14">
                <h1 class="font-hatton text-main text-4xl md:text-5xl leading-tight">Commenter : <?= htmlspecialchars($selected['title']) ?></h1>
            </header>

            <div class="rounded-[30px] bg-div px-6 py-8 md:px-10 md:py-12 shadow-xl/20">
                <form action="comment.php?article=<?= urlencode($slug) ?>" method="post" class="space-y-4">
                    <div>
                        <label for="comment" class="mb-2 block font-hatton text-lg">Votre commentaire</label>
                        <textarea id="comment" name="comment" rows="6" required class="w-full rounded-lg border px-4 py-3"></textarea>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="rounded-full bg-button px-6 py-3 font-hatton">Envoyer</button>
                        <a href="blog.php?article=<?= urlencode($slug) ?>#post-<?= urlencode($slug) ?>" class="rounded-full border border-div px-6 py-3 font-hatton">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php include(__DIR__ . '/../headers/footer.php'); ?>
