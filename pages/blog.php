<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

$blogPosts = [
    [
        'slug' => '5-etapes-peau-radieuse',
        'title' => '5 Étapes pour une Peau Radieuse',
        'excerpt' => 'Découvrez nos conseils d’experts pour maintenir une peau éclatante.',
        'likes' => 124,
        'comments' => 18,
        'category' => 'Conseils',
        'read_time' => '4 min',
        'date' => '03 avril 2026',
        'content' => [
            'Une peau lumineuse commence par une routine simple, régulière et adaptée à votre type de peau. Inutile d’accumuler les produits: la constance compte davantage que la quantité.',
            'Première étape: nettoyer en douceur matin et soir pour éliminer les impuretés sans fragiliser la barrière cutanée. Deuxième étape: appliquer un sérum ciblé selon vos besoins, qu’il s’agisse d’éclat, d’hydratation ou d’apaisement.',
            'Troisième étape: hydrater avec une crème adaptée afin de maintenir le confort de la peau. Quatrième étape: intégrer une exfoliation douce une à deux fois par semaine pour affiner le grain de peau. Enfin, cinquième étape: protéger la peau avec une protection solaire en journée.',
            'Cette base peut ensuite être enrichie avec des gestes experts en institut pour prolonger les résultats et installer une routine réellement durable.'
        ]
    ],
    [
        'slug' => 'science-de-nos-serums',
        'title' => 'La Science de nos Sérums',
        'excerpt' => 'Apprenez tout sur les ingrédients puissants de nos produits.',
        'likes' => 89,
        'comments' => 12,
        'category' => 'Ingrédients',
        'read_time' => '5 min',
        'date' => '29 mars 2026',
        'content' => [
            'Les sérums sont conçus pour délivrer des actifs concentrés avec une texture légère et rapide à absorber. Ils viennent compléter la crème, pas la remplacer.',
            'Un sérum hydratant agit principalement sur le confort et la souplesse de la peau, tandis qu’un sérum éclat vise à améliorer l’apparence du teint. La différence se joue dans la sélection des actifs et leur concentration.',
            'Pour tirer le meilleur parti d’un sérum, il faut l’appliquer sur peau propre, avant la crème, avec quelques gouttes seulement. Une utilisation régulière permet d’obtenir un résultat plus visible qu’une application occasionnelle.',
            'Le bon sérum n’est pas forcément le plus fort, mais celui qui répond précisément au besoin réel de votre peau.'
        ]
    ],
    [
        'slug' => 'routine-matin-vs-soir',
        'title' => 'Routine Matin vs Soir',
        'excerpt' => 'Comprenez la différence entre les routines A.M et P.M.',
        'likes' => 156,
        'comments' => 24,
        'category' => 'Routine',
        'read_time' => '3 min',
        'date' => '24 mars 2026',
        'content' => [
            'La routine du matin a pour objectif principal de protéger la peau et de la préparer à la journée. Celle du soir vise plutôt à nettoyer, réparer et nourrir.',
            'Le matin, on privilégie un nettoyage léger, un sérum adapté, une crème hydratante et une protection solaire. Cette routine doit rester efficace sans être trop lourde.',
            'Le soir, la priorité est de retirer les résidus accumulés dans la journée, puis d’apporter des soins plus riches ou plus ciblés. C’est souvent le meilleur moment pour les textures cocon et les actifs de renouvellement.',
            'Distinguer les deux moments permet d’éviter les routines trop chargées et d’utiliser chaque produit au moment le plus pertinent.'
        ]
    ],
];

$validSlugs = array_column($blogPosts, 'slug');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_article'])) {
    $likedSlug = $_POST['article_slug'] ?? '';

    if (!isset($_SESSION['id_user'])) {
        redirect('../auth/login.php', 'error', 'Vous devez vous connecter pour liker un article.');
    }

    if (in_array($likedSlug, $validSlugs, true)) {
        $stmt = $pdo->prepare('SELECT id_like FROM BLOG_LIKE WHERE article_slug = ? AND id_user = ?');
        $stmt->execute([$likedSlug, $_SESSION['id_user']]);
        $existingLike = $stmt->fetch();

        if ($existingLike) {
            $stmt = $pdo->prepare('DELETE FROM BLOG_LIKE WHERE article_slug = ? AND id_user = ?');
            $stmt->execute([$likedSlug, $_SESSION['id_user']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO BLOG_LIKE (article_slug, id_user) VALUES (?, ?)');
            $stmt->execute([$likedSlug, $_SESSION['id_user']]);
        }
    }

    $redirectUrl = 'blog.php';
    // add fragment to preserve scroll position
    if (!empty($likedSlug)) {
        if (!empty($_POST['from_list'])) {
            $redirectUrl .= '#post-' . urlencode($likedSlug);
        } else {
            $redirectUrl .= '?article=' . urlencode($likedSlug) . '#post-' . urlencode($likedSlug);
        }
    }
    redirect($redirectUrl);
}

$likeCounts = array_fill_keys($validSlugs, 0);
$likedArticles = [];
$placeholders = implode(',', array_fill(0, count($validSlugs), '?'));

$stmt = $pdo->prepare("SELECT article_slug, COUNT(*) AS total FROM BLOG_LIKE WHERE article_slug IN ($placeholders) GROUP BY article_slug");
$stmt->execute($validSlugs);
foreach ($stmt->fetchAll() as $likeRow) {
    $likeCounts[$likeRow['article_slug']] = (int) $likeRow['total'];
}

// Comment counts (guarded if table missing)
$commentCounts = array_fill_keys($validSlugs, 0);
try {
    $stmt = $pdo->prepare("SELECT article_slug, COUNT(*) AS total FROM BLOG_COMMENT WHERE article_slug IN ($placeholders) GROUP BY article_slug");
    $stmt->execute($validSlugs);
    foreach ($stmt->fetchAll() as $cRow) {
        $commentCounts[$cRow['article_slug']] = (int) $cRow['total'];
    }
} catch (PDOException $e) {
    // Table may not exist yet — keep default counts (from static data)
}

if (isset($_SESSION['id_user'])) {
    $params = array_merge([$_SESSION['id_user']], $validSlugs);
    $stmt = $pdo->prepare("SELECT article_slug FROM BLOG_LIKE WHERE id_user = ? AND article_slug IN ($placeholders)");
    $stmt->execute($params);
    $likedArticles = array_column($stmt->fetchAll(), 'article_slug');
}

foreach ($blogPosts as &$post) {
    $post['likes'] = $likeCounts[$post['slug']] ?? 0;
    $post['liked_by_user'] = in_array($post['slug'], $likedArticles, true);
    $post['comments'] = $commentCounts[$post['slug']] ?? $post['comments'];
}
unset($post);

$selectedPost = null;
$requestedSlug = $_GET['article'] ?? null;

if ($requestedSlug !== null) {
    foreach ($blogPosts as $post) {
        if ($post['slug'] === $requestedSlug) {
            $selectedPost = $post;
            break;
        }
    }
}

// fetch comments for selected post (guarded)
$selectedPostComments = [];
if ($selectedPost) {
    try {
        $stmt = $pdo->prepare('SELECT c.*, u.prenom, u.nom FROM BLOG_COMMENT c JOIN UTILISATEUR u ON c.id_user = u.id_user WHERE c.article_slug = ? ORDER BY c.created_at DESC');
        $stmt->execute([$selectedPost['slug']]);
        $selectedPostComments = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Table may not exist yet — leave comments empty
        $selectedPostComments = [];
    }
}

include(__DIR__ . '/../headers/header.php');
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-10 md:pt-16">
        <div class="mx-auto max-w-5xl">
            <header class="mb-10 text-center md:mb-14">
                <p class="font-hatton text-sm uppercase tracking-[0.35em] mb-4">Journal beauté</p>
                <h1 class="font-hatton text-main text-4xl md:text-6xl leading-tight">Blog &amp; Actualités</h1>
            </header>

            <?php if ($selectedPost): ?>
                <section class="mb-8">
                    <a href="blog.php"
                        class="inline-flex items-center justify-center rounded-full border border-div bg-[#F5F2ED] px-6 py-3 font-hatton text-main transition-all duration-300 hover:scale-105">
                        Retour aux articles
                    </a>
                </section>

                <article id="post-<?= htmlspecialchars($selectedPost['slug']) ?>" class="rounded-[36px] md:rounded-[48px] bg-div px-6 py-8 md:px-10 md:py-12 shadow-xl/20">
                    <div class="flex flex-wrap items-center gap-3 mb-6">
                        <span class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main text-sm">
                            <?= htmlspecialchars($selectedPost['category']) ?>
                        </span>
                        <span class="font-hatton text-sm">
                            <?= htmlspecialchars($selectedPost['date']) ?> • <?= htmlspecialchars($selectedPost['read_time']) ?> de lecture
                        </span>
                    </div>

                    <h2 class="font-hatton text-main text-4xl md:text-5xl leading-tight mb-5">
                        <?= htmlspecialchars($selectedPost['title']) ?>
                    </h2>
                    <p class="font-hatton text-lg md:text-xl leading-relaxed mb-8 max-w-3xl">
                        <?= htmlspecialchars($selectedPost['excerpt']) ?>
                    </p>

                    <div class="flex items-center gap-6 mb-10">
                        <form action="blog.php<?= $requestedSlug ? '?article=' . urlencode($selectedPost['slug']) : '' ?>" method="post" class="inline-flex items-center gap-2">
                            <input type="hidden" name="article_slug" value="<?= htmlspecialchars($selectedPost['slug']) ?>">
                            <input type="hidden" name="like_article" value="1">
                            <button type="submit" class="inline-flex items-center gap-2 font-hatton rounded-full px-4 py-2 <?= $selectedPost['liked_by_user'] ? 'bg-red-600 text-white' : 'bg-button text-main' ?> shadow-md hover:scale-105 transition-all duration-200" aria-label="Like">
                                <?php if ($selectedPost['liked_by_user']): ?>
                                    <svg class="h-5 w-5 text-current" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.7">
                                        <path d="M12 20.5s-7-4.35-7-10.16A4.34 4.34 0 0 1 9.34 6 4.85 4.85 0 0 1 12 7.56 4.85 4.85 0 0 1 14.66 6 4.34 4.34 0 0 1 19 10.34C19 16.15 12 20.5 12 20.5Z" />
                                    </svg>
                                <?php else: ?>
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                        <path d="M12 20.5s-7-4.35-7-10.16A4.34 4.34 0 0 1 9.34 6 4.85 4.85 0 0 1 12 7.56 4.85 4.85 0 0 1 14.66 6 4.34 4.34 0 0 1 19 10.34C19 16.15 12 20.5 12 20.5Z" />
                                    </svg>
                                <?php endif; ?>
                                <span class="font-hatton font-semibold"><?= $selectedPost['likes'] ?></span>
                            </button>
                        </form>

                        <a href="comment.php?article=<?= urlencode($selectedPost['slug']) ?>" class="inline-flex items-center gap-2 font-hatton rounded-full px-3 py-1 bg-button text-main shadow-md hover:scale-105 transition-all duration-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.47 8.47 0 0 1-3.42-.72L3 21l1.72-6.08A8.47 8.47 0 0 1 4 11.5 8.5 8.5 0 1 1 21 11.5Z" />
                            </svg>
                            <span class="font-hatton font-semibold"><?= $selectedPost['comments'] ?></span>
                        </a>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                        <div class="rounded-[30px] bg-[#E8E2D9] p-6 md:p-8">
                            <div class="space-y-5">
                                <?php foreach ($selectedPost['content'] as $paragraph): ?>
                                    <p class="font-hatton text-main leading-relaxed text-lg">
                                        <?= htmlspecialchars($paragraph) ?>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <aside class="space-y-5">
                            <div class="rounded-[30px] bg-[#E8E2D9] p-6">
                                <p class="font-hatton text-sm uppercase tracking-[0.25em] mb-2">À retenir</p>
                                <p class="font-hatton text-main leading-relaxed">
                                    Une routine claire, des actifs bien choisis et une application régulière produisent de meilleurs résultats qu’une accumulation de soins.
                                </p>
                            </div>

                            <div class="rounded-[30px] bg-[#E8E2D9] p-6">
                                <p class="font-hatton text-sm uppercase tracking-[0.25em] mb-2">Interaction</p>
                                <p class="font-hatton text-main leading-relaxed">
                                    Le système de likes et de commentaires sera branché plus tard. La structure visuelle est déjà prévue ici.
                                </p>
                            </div>
                        </aside>
                    </div>

                    <div class="mt-8">
                        <h3 class="font-hatton text-2xl mb-4">Commentaires (<?= count($selectedPostComments) ?>)</h3>
                        <div class="space-y-4">
                            <?php if (empty($selectedPostComments)): ?>
                                <p class="font-hatton text-main">Aucun commentaire pour le moment.</p>
                            <?php else: ?>
                                <?php foreach ($selectedPostComments as $c): ?>
                                    <div class="rounded-lg bg-[#F5F2ED] p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <strong class="font-hatton"><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
                                            <div class="flex items-center gap-3">
                                                <span class="text-sm text-[#7a6a58]"><?= htmlspecialchars($c['created_at']) ?></span>
                                                <?php if (isset($_SESSION['id_user']) && $_SESSION['id_user'] == $c['id_user']): ?>
                                                    <form action="delete_comment.php" method="post" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                                        <input type="hidden" name="comment_id" value="<?= (int)$c['id_comment'] ?>">
                                                        <input type="hidden" name="article_slug" value="<?= htmlspecialchars($selectedPost['slug']) ?>">
                                                        <button type="submit" class="text-sm text-red-600">Supprimer</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <p class="font-hatton text-main"><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php else: ?>
                <section class="space-y-6 md:space-y-7">
                    <?php foreach ($blogPosts as $post): ?>
                        <a id="post-<?= htmlspecialchars($post['slug']) ?>" href="blog.php?article=<?= urlencode($post['slug']) ?>"
                            class="group block rounded-[30px] md:rounded-[34px] bg-div px-6 py-6 md:px-8 md:py-7 shadow-xl/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl/20">
                            <article class="flex flex-col gap-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main text-sm">
                                        <?= htmlspecialchars($post['category']) ?>
                                    </span>
                                    <span class="font-hatton text-sm">
                                        <?= htmlspecialchars($post['date']) ?> • <?= htmlspecialchars($post['read_time']) ?> de lecture
                                    </span>
                                </div>

                                <div>
                                    <h2 class="font-hatton text-main text-3xl md:text-4xl mb-2 transition-colors duration-300 group-hover:text-[#F5F2ED]">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </h2>
                                    <p class="font-hatton text-lg leading-relaxed">
                                        <?= htmlspecialchars($post['excerpt']) ?>
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-4 pt-2">
                                    <div class="flex items-center gap-6">
                                        <form action="blog.php" method="post" class="inline-flex items-center gap-2">
                                            <input type="hidden" name="article_slug" value="<?= htmlspecialchars($post['slug']) ?>">
                                            <input type="hidden" name="like_article" value="1">
                                            <input type="hidden" name="from_list" value="1">
                                            <button type="submit" onclick="event.stopPropagation();" class="inline-flex items-center gap-2 font-hatton rounded-full px-3 py-1 <?= $post['liked_by_user'] ? 'bg-red-600 text-white' : 'bg-button text-main' ?> shadow-md hover:scale-105 transition-all duration-200" aria-label="Like">
                                                <?php if ($post['liked_by_user']): ?>
                                                    <svg class="h-5 w-5 text-current" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.7">
                                                        <path d="M12 20.5s-7-4.35-7-10.16A4.34 4.34 0 0 1 9.34 6 4.85 4.85 0 0 1 12 7.56 4.85 4.85 0 0 1 14.66 6 4.34 4.34 0 0 1 19 10.34C19 16.15 12 20.5 12 20.5Z" />
                                                    </svg>
                                                <?php else: ?>
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                                        <path d="M12 20.5s-7-4.35-7-10.16A4.34 4.34 0 0 1 9.34 6 4.85 4.85 0 0 1 12 7.56 4.85 4.85 0 0 1 14.66 6 4.34 4.34 0 0 1 19 10.34C19 16.15 12 20.5 12 20.5Z" />
                                                    </svg>
                                                <?php endif; ?>
                                                <span class="font-hatton font-semibold"><?= $post['likes'] ?></span>
                                            </button>
                                        </form>
                                        <form action="comment.php" method="get" class="inline-flex items-center" onsubmit="event.stopPropagation();">
                                            <input type="hidden" name="article" value="<?= htmlspecialchars($post['slug']) ?>">
                                            <button type="submit" onclick="event.stopPropagation();" class="inline-flex items-center gap-2 font-hatton rounded-full px-3 py-1 bg-button text-main shadow-md hover:scale-105 transition-all duration-200" aria-label="Comment">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                                    <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 8.47 8.47 0 0 1-3.42-.72L3 21l1.72-6.08A8.47 8.47 0 0 1 4 11.5 8.5 8.5 0 1 1 21 11.5Z" />
                                                </svg>
                                                <span class="font-hatton font-semibold"><?= $post['comments'] ?></span>
                                            </button>
                                        </form>
                                    </div>

                                    <span class="font-hatton text-main text-sm uppercase tracking-[0.25em] transition-colors duration-300 group-hover:text-[#F5F2ED]">
                                        Lire l’article
                                    </span>
                                </div>
                            </article>
                        </a>
                    <?php endforeach; ?>
                </section>

                <section class="mt-12 md:mt-16">
                    <div class="rounded-[34px] md:rounded-[38px] bg-div px-6 py-10 text-center shadow-xl/20 md:px-10 md:py-12">
                        <h2 class="font-hatton text-main text-4xl md:text-5xl mb-4">Événements à venir</h2>
                        <p class="font-hatton text-lg leading-relaxed max-w-2xl mx-auto mb-8">
                            Rejoignez-nous pour notre atelier exclusif du 28 mars et découvrez nos rituels soin dans une ambiance intimiste.
                        </p>
                        <a href="#"
                            class="inline-flex items-center justify-center rounded-full bg-[#E8E2D9] px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:bg-[#F5F2ED]">
                            S’inscrire
                        </a>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
