<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

if (isset($_SESSION['id_user'])) {
    $stmtUser = $pdo->prepare('SELECT is_banned FROM UTILISATEUR WHERE id_user = ?');
    $stmtUser->execute([$_SESSION['id_user']]);
    $user = $stmtUser->fetch();
    if ($user && $user['is_banned']) {
        redirect('../index.php', 'error', 'Vous avez été banni et ne pouvez plus accéder au blog.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_article'])) {
    if (!isset($_SESSION['id_user'])) {
        redirect('../auth/login.php', 'error', 'Vous devez vous connecter pour liker un article.');
    }

    $stmtUser = $pdo->prepare('SELECT is_banned FROM UTILISATEUR WHERE id_user = ?');
    $stmtUser->execute([$_SESSION['id_user']]);
    $user = $stmtUser->fetch();
    if ($user && $user['is_banned']) {
        redirect('blog.php', 'error', 'Vous êtes banni et ne pouvez pas effectuer cette action.');
    }

    $articleId = (int)$_POST['article_id'] ?? 0;
    
    $stmt = $pdo->prepare('SELECT id_article FROM BLOG_ARTICLE WHERE id_article = ?');
    $stmt->execute([$articleId]);
    
    if ($stmt->rowCount() === 0) {
        redirect('blog.php', 'error', 'Article non trouvé.');
    }

    $stmt = $pdo->prepare('SELECT id_like FROM BLOG_LIKE WHERE article_id = ? AND id_user = ?');
    $stmt->execute([$articleId, $_SESSION['id_user']]);
    
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare('DELETE FROM BLOG_LIKE WHERE article_id = ? AND id_user = ?');
        $stmt->execute([$articleId, $_SESSION['id_user']]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO BLOG_LIKE (article_id, id_user) VALUES (?, ?)');
        $stmt->execute([$articleId, $_SESSION['id_user']]);
    }

    $redirectUrl = 'blog.php';
    if (!empty($_POST['article_id'])) {
        $redirectUrl .= '?article=' . (int)$_POST['article_id'] . '#article-' . (int)$_POST['article_id'];
    }
    redirect($redirectUrl);
}

$stmt = $pdo->prepare('SELECT article_id, COUNT(*) AS total FROM BLOG_LIKE GROUP BY article_id');
$stmt->execute();
$likeCounts = [];
foreach ($stmt->fetchAll() as $row) {
    $likeCounts[$row['article_id']] = (int)$row['total'];
}

$likedArticles = [];
if (isset($_SESSION['id_user'])) {
    $stmt = $pdo->prepare('SELECT article_id FROM BLOG_LIKE WHERE id_user = ?');
    $stmt->execute([$_SESSION['id_user']]);
    $likedArticles = array_column($stmt->fetchAll(), 'article_id');
}

$selectedArticle = null;
$requestedId = $_GET['article'] ?? null;

if ($requestedId !== null) {
    $stmt = $pdo->prepare('SELECT * FROM BLOG_ARTICLE WHERE id_article = ?');
    $stmt->execute([(int)$requestedId]);
    $selectedArticle = $stmt->fetch();
}

$selectedComments = [];
$commentLikeCounts = [];
$likedCommentIds = [];
if ($selectedArticle) {
    $stmt = $pdo->prepare('SELECT c.*, u.prenom, u.nom FROM BLOG_COMMENT c JOIN UTILISATEUR u ON c.id_user = u.id_user WHERE c.article_id = ? ORDER BY c.created_at DESC');
    $stmt->execute([$selectedArticle['id_article']]);
    $selectedComments = $stmt->fetchAll();

    if (!empty($selectedComments)) {
        $commentIds = array_column($selectedComments, 'id_comment');
        $placeholders = implode(',', array_fill(0, count($commentIds), '?'));

        $stmt = $pdo->prepare("SELECT comment_id, COUNT(*) AS total FROM BLOG_COMMENT_LIKE WHERE comment_id IN ($placeholders) GROUP BY comment_id");
        $stmt->execute($commentIds);
        foreach ($stmt->fetchAll() as $row) {
            $commentLikeCounts[$row['comment_id']] = (int)$row['total'];
        }

        if (isset($_SESSION['id_user'])) {
            $params = array_merge([$_SESSION['id_user']], $commentIds);
            $stmt = $pdo->prepare("SELECT comment_id FROM BLOG_COMMENT_LIKE WHERE id_user = ? AND comment_id IN ($placeholders)");
            $stmt->execute($params);
            $likedCommentIds = array_column($stmt->fetchAll(), 'comment_id');
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM BLOG_ARTICLE ORDER BY date_publication DESC');
$stmt->execute();
$allArticles = $stmt->fetchAll();

include(__DIR__ . '/../headers/header.php');
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-10 md:pt-16">
        <div class="mx-auto max-w-5xl">
            <header class="mb-10 text-center md:mb-14">
                <p class="font-hatton text-sm uppercase tracking-[0.35em] mb-4">Journal beauté</p>
                <h1 class="font-hatton text-main text-4xl md:text-6xl leading-tight">Blog &amp; Actualités</h1>
            </header>

            <?php if ($selectedArticle): ?>
                <section class="mb-8">
                    <a href="blog.php" class="inline-flex items-center justify-center rounded-full border border-div bg-[#F5F2ED] px-6 py-3 font-hatton text-main transition-all duration-300 hover:scale-105">
                        ← Retour aux articles
                    </a>
                </section>

                <article id="article-<?= $selectedArticle['id_article'] ?>" class="rounded-3xl bg-div px-6 py-8 md:px-10 md:py-12">
                    <div class="mb-6 flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main text-sm">
                            <?= htmlspecialchars($selectedArticle['categorie']) ?>
                        </span>
                        <span class="font-hatton text-sm">
                            <?= date('d F Y', strtotime($selectedArticle['date_publication'])) ?> • <?= htmlspecialchars($selectedArticle['temps_lecture']) ?> de lecture
                        </span>
                    </div>

                    <h2 class="font-hatton text-main text-4xl md:text-5xl mb-5 leading-tight">
                        <?= htmlspecialchars($selectedArticle['titre']) ?>
                    </h2>

                    <p class="font-hatton text-lg md:text-xl leading-relaxed mb-8 max-w-3xl">
                        <?= htmlspecialchars($selectedArticle['excerpt']) ?>
                    </p>

                    <div class="mb-10 flex items-center gap-3">
                        <form action="blog.php?article=<?= $selectedArticle['id_article'] ?>" method="post" class="inline">
                            <input type="hidden" name="article_id" value="<?= $selectedArticle['id_article'] ?>">
                            <input type="hidden" name="like_article" value="1">
                            <button type="submit" class="inline-flex items-center gap-2 font-hatton rounded-full px-4 py-2 <?= in_array($selectedArticle['id_article'], $likedArticles) ? 'bg-red-600 text-white' : 'bg-button text-main' ?> shadow-md hover:scale-105 transition-all">
                                <span><?= in_array($selectedArticle['id_article'], $likedArticles) ? '❤️' : '🤍' ?></span>
                                <span><?= $likeCounts[$selectedArticle['id_article']] ?? 0 ?></span>
                            </button>
                        </form>
                    </div>

                    <div class="rounded-2xl bg-[#E8E2D9] p-6 md:p-8">
                        <div class="space-y-5">
                            <?php foreach (explode("\n\n", $selectedArticle['contenu']) as $para): ?>
                                <p class="font-hatton text-main text-lg leading-relaxed">
                                    <?= htmlspecialchars($para) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-12 border-t border-gray-300 pt-8">
                        <h3 class="font-hatton text-2xl mb-6">Commentaires (<?= count($selectedComments) ?>)</h3>

                        <?php if (!empty($selectedComments)): ?>
                            <div class="space-y-4 mb-8">
                                <?php foreach ($selectedComments as $comment): ?>
                                    <div class="rounded-lg bg-[#F5F2ED] p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <strong><?= htmlspecialchars($comment['prenom'] . ' ' . $comment['nom']) ?></strong>
                                            <span class="text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                        </div>
                                        <p class="font-hatton"><?= htmlspecialchars($comment['contenu']) ?></p>
                                        <div class="mt-4 flex items-center justify-between">
                                            <form action="like_comment.php" method="post" class="inline">
                                                <input type="hidden" name="comment_id" value="<?= $comment['id_comment'] ?>">
                                                <input type="hidden" name="article_id" value="<?= $selectedArticle['id_article'] ?>">
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 font-hatton text-sm <?= in_array($comment['id_comment'], $likedCommentIds) ? 'bg-red-600 text-white border-red-600' : 'bg-white text-main border-gray-300' ?> transition-all duration-200">
                                                    <span><?= in_array($comment['id_comment'], $likedCommentIds) ? '❤️' : '🤍' ?></span>
                                                    <span><?= $commentLikeCounts[$comment['id_comment']] ?? 0 ?></span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['id_user'])): ?>
                            <form method="post" action="comment.php" class="mt-6">
                                <input type="hidden" name="article_id" value="<?= $selectedArticle['id_article'] ?>">
                                <textarea name="contenu" required placeholder="Votre commentaire..." class="w-full rounded-lg border border-gray-300 p-4 font-hatton" rows="4"></textarea>
                                <button type="submit" class="mt-3 rounded-full bg-button px-6 py-2 font-hatton text-main hover:scale-105 transition-all">
                                    Publier commentaire
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="font-hatton text-gray-600">
                                <a href="<?= url('/auth/login.php') ?>" class="text-button underline">Connectez-vous</a> pour commenter.
                            </p>
                        <?php endif; ?>
                    </div>
                </article>

            <?php else: ?>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($allArticles as $article): ?>
                        <a href="blog.php?article=<?= $article['id_article'] ?>" class="group rounded-2xl overflow-hidden bg-div shadow-lg hover:shadow-xl transition-all">
                            <div class="p-6 h-full flex flex-col">
                                <span class="inline-block rounded-full bg-[#E8E2D9] px-3 py-1 font-hatton text-xs w-fit mb-4">
                                    <?= htmlspecialchars($article['categorie']) ?>
                                </span>
                                
                                <h3 class="font-hatton text-main text-lg mb-3 group-hover:text-button transition-colors">
                                    <?= htmlspecialchars($article['titre']) ?>
                                </h3>
                                
                                <p class="font-hatton text-gray-700 text-sm mb-4 flex-grow">
                                    <?= htmlspecialchars($article['excerpt']) ?>
                                </p>
                                
                                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                    <span class="text-xs text-gray-600"><?= $article['temps_lecture'] ?></span>
                                    <span class="font-hatton text-sm text-red-500">❤ <?= $likeCounts[$article['id_article']] ?? 0 ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include(__DIR__ . '/../headers/footer.php'); ?>
