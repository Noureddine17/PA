<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    redirect('../index.php', 'error', 'Accès refusé.');
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'ajouter') {
            $titre = $_POST['titre'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $excerpt = $_POST['excerpt'] ?? '';
            $contenu = $_POST['contenu'] ?? '';
            $categorie = $_POST['categorie'] ?? '';
            $temps_lecture = $_POST['temps_lecture'] ?? '';

            if (empty($titre) || empty($slug) || empty($contenu)) {
                $message = 'Veuillez remplir tous les champs obligatoires.';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare('INSERT INTO BLOG_ARTICLE (titre, slug, excerpt, contenu, categorie, temps_lecture) VALUES (?, ?, ?, ?, ?, ?)');
                if ($stmt->execute([$titre, $slug, $excerpt, $contenu, $categorie, $temps_lecture])) {
                    $message = 'Article ajouté avec succès !';
                    $messageType = 'success';
                } else {
                    $message = 'Erreur lors de l\'ajout de l\'article.';
                    $messageType = 'error';
                }
            }
        } elseif ($action === 'modifier') {
            $id = (int)$_POST['id'] ?? 0;
            $titre = $_POST['titre'] ?? '';
            $excerpt = $_POST['excerpt'] ?? '';
            $contenu = $_POST['contenu'] ?? '';
            $categorie = $_POST['categorie'] ?? '';
            $temps_lecture = $_POST['temps_lecture'] ?? '';

            if (empty($titre) || empty($contenu) || $id === 0) {
                $message = 'Données invalides.';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare('UPDATE BLOG_ARTICLE SET titre = ?, excerpt = ?, contenu = ?, categorie = ?, temps_lecture = ? WHERE id_article = ?');
                if ($stmt->execute([$titre, $excerpt, $contenu, $categorie, $temps_lecture, $id])) {
                    $message = 'Article modifié avec succès !';
                    $messageType = 'success';
                } else {
                    $message = 'Erreur lors de la modification.';
                    $messageType = 'error';
                }
            }
        } elseif ($action === 'supprimer') {
            $id = (int)$_POST['id'] ?? 0;

            if ($id === 0) {
                $message = 'ID invalide.';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare('DELETE FROM BLOG_ARTICLE WHERE id_article = ?');
                if ($stmt->execute([$id])) {
                    $message = 'Article supprimé avec succès !';
                    $messageType = 'success';
                } else {
                    $message = 'Erreur lors de la suppression.';
                    $messageType = 'error';
                }
            }
        }
    }
}

$editArticle = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM BLOG_ARTICLE WHERE id_article = ?');
    $stmt->execute([$id]);
    $editArticle = $stmt->fetch();
}

$stmt = $pdo->prepare('SELECT * FROM BLOG_ARTICLE ORDER BY date_publication DESC');
$stmt->execute();
$articles = $stmt->fetchAll();

include(__DIR__ . '/../headers/header.php');
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-10">
        <div class="mx-auto max-w-4xl">
            <h1 class="font-hatton text-main text-4xl mb-8">Gestion des Articles</h1>

            <?php if ($message): ?>
                <div class="mb-6 rounded-lg p-4 <?= $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="grid md:grid-cols-2 gap-8">
                <div class="rounded-lg bg-div p-6">
                    <h2 class="font-hatton text-2xl mb-4">
                        <?= $editArticle ? 'Modifier l\'article' : 'Nouvel article' ?>
                    </h2>

                    <form method="post" class="space-y-4">
                        <input type="hidden" name="action" value="<?= $editArticle ? 'modifier' : 'ajouter' ?>">
                        <?php if ($editArticle): ?>
                            <input type="hidden" name="id" value="<?= $editArticle['id_article'] ?>">
                        <?php endif; ?>

                        <div>
                            <label class="block font-hatton mb-2">Titre *</label>
                            <input type="text" name="titre" required value="<?= htmlspecialchars($editArticle['titre'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 p-2 font-hatton">
                        </div>

                        <?php if (!$editArticle): ?>
                            <div>
                                <label class="block font-hatton mb-2">Slug (URL) *</label>
                                <input type="text" name="slug" required class="w-full rounded-lg border border-gray-300 p-2 font-hatton" placeholder="ex: mon-article">
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="block font-hatton mb-2">Extrait (court résumé) *</label>
                            <textarea name="excerpt" required rows="3" class="w-full rounded-lg border border-gray-300 p-2 font-hatton"><?= htmlspecialchars($editArticle['excerpt'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="block font-hatton mb-2">Contenu *</label>
                            <textarea name="contenu" required rows="6" class="w-full rounded-lg border border-gray-300 p-2 font-hatton" placeholder="Séparez les paragraphes par une ligne vide"><?= htmlspecialchars($editArticle['contenu'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="block font-hatton mb-2">Catégorie</label>
                            <input type="text" name="categorie" value="<?= htmlspecialchars($editArticle['categorie'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 p-2 font-hatton" placeholder="ex: Conseils">
                        </div>

                        <div>
                            <label class="block font-hatton mb-2">Temps de lecture</label>
                            <input type="text" name="temps_lecture" value="<?= htmlspecialchars($editArticle['temps_lecture'] ?? '') ?>" class="w-full rounded-lg border border-gray-300 p-2 font-hatton" placeholder="ex: 5 min">
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-button px-4 py-2 font-hatton text-main hover:scale-105 transition-all">
                            <?= $editArticle ? 'Modifier' : 'Ajouter' ?>
                        </button>
                    </form>

                    <?php if ($editArticle): ?>
                        <a href="articles.php" class="mt-4 block text-center rounded-lg border border-gray-300 px-4 py-2 font-hatton hover:bg-gray-100">
                            Annuler
                        </a>
                    <?php endif; ?>
                </div>

                <div>
                    <h2 class="font-hatton text-2xl mb-4">Articles (<?= count($articles) ?>)</h2>

                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php foreach ($articles as $article): ?>
                            <div class="rounded-lg bg-div p-4 flex items-center justify-between">
                                <div>
                                    <h3 class="font-hatton font-bold"><?= htmlspecialchars($article['titre']) ?></h3>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($article['categorie']) ?></p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="articles.php?edit=<?= $article['id_article'] ?>" class="rounded-lg bg-button px-3 py-1 font-hatton text-sm text-main hover:scale-105 transition-all">
                                        ✎
                                    </a>
                                    <form method="post" onsubmit="return confirm('Supprimer cet article ?');" style="display: inline;">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id" value="<?= $article['id_article'] ?>">
                                        <button type="submit" class="rounded-lg bg-red-500 px-3 py-1 font-hatton text-sm text-white hover:scale-105 transition-all">
                                            ✕
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include(__DIR__ . '/../headers/footer.php'); ?>
