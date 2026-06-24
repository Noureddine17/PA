<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

requireRole($pdo, 'admin', '../auth/login.php', 'client.php', 'Accès réservé aux administrateurs.');

$productId = (int) ($_GET['id'] ?? 0);
$product = [
    'id_produit' => 0,
    'nom' => '',
    'type_produit' => '',
    'prix' => '',
    'stock' => '',
    'subtitle' => '',
    'description' => '',
    'image' => '',
];

if ($productId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM PRODUIT WHERE id_produit = ?');
    $stmt->execute([$productId]);
    $productFound = $stmt->fetch();

    if (!$productFound) {
        redirect('products.php', 'error', 'Produit introuvable.');
    }

    $product = $productFound;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $idProduit = (int) ($_POST['id_produit'] ?? 0);

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM PRODUIT WHERE id_produit = ?');
        $stmt->execute([$idProduit]);

        redirect('products.php', 'success', 'Produit supprimé.');
    }

    $nom = trim($_POST['nom'] ?? '');
    $type = trim($_POST['type_produit'] ?? '');
    $prix = (float) ($_POST['prix'] ?? 0);
    $stock = (int) ($_POST['stock'] ?? 0);
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = trim($_POST['current_image'] ?? '');
    $imageUpload = $_FILES['image'] ?? null;

    if ($imageUpload && $imageUpload['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($imageUpload['name'], PATHINFO_EXTENSION));
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $extensionsAutorisees)) {
            redirect('products.php' . ($idProduit > 0 ? '?id=' . $idProduit : ''), 'error', 'Format image invalide.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $typeImage = finfo_file($finfo, $imageUpload['tmp_name']);
        finfo_close($finfo);

        $typesAutorises = ['image/jpeg', 'image/png', 'image/x-png', 'image/webp'];

        if (!in_array($typeImage, $typesAutorises)) {
            redirect('products.php' . ($idProduit > 0 ? '?id=' . $idProduit : ''), 'error', 'Le fichier doit être une vraie image.');
        }

        $nomFichier = 'product-' . time() . '-' . random_int(1000, 9999) . '.' . $extension;
        $dossierUpload = __DIR__ . '/../assets/images/shop/';
        $cheminFinal = $dossierUpload . $nomFichier;

        if (!is_writable($dossierUpload)) {
            redirect('products.php' . ($idProduit > 0 ? '?id=' . $idProduit : ''), 'error', 'Le dossier des images ne permet pas l\'enregistrement.');
        }

        if (!move_uploaded_file($imageUpload['tmp_name'], $cheminFinal)) {
            redirect('products.php' . ($idProduit > 0 ? '?id=' . $idProduit : ''), 'error', 'Image impossible à enregistrer.');
        }

        $image = '../assets/images/shop/' . $nomFichier;
    }

    if ($nom === '' || $type === '' || $prix <= 0 || $description === '' || $image === '') {
        redirect('products.php' . ($idProduit > 0 ? '?id=' . $idProduit : ''), 'error', 'Les champs principaux sont obligatoires.');
    }

    try {
        if ($action === 'save' && $idProduit > 0) {
            $stmt = $pdo->prepare('
                UPDATE PRODUIT
                SET nom = ?, type_produit = ?, prix = ?, stock = ?, subtitle = ?, description = ?, image = ?
                WHERE id_produit = ?
            ');
            $stmt->execute([$nom, $type, $prix, $stock, $subtitle, $description, $image, $idProduit]);

            redirect('products.php', 'success', 'Produit modifié.');
        }

        $stmt = $pdo->prepare('
            INSERT INTO PRODUIT (nom, type_produit, prix, stock, subtitle, description, image)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$nom, $type, $prix, $stock, $subtitle, $description, $image]);
    } catch (PDOException $e) {
        redirect('products.php' . ($idProduit > 0 ? '?id=' . $idProduit : ''), 'error', 'Un produit avec ce nom existe déjà.');
    }

    redirect('products.php', 'success', 'Produit ajouté.');
}

$stmt = $pdo->query('SELECT * FROM PRODUIT ORDER BY id_produit DESC');
$products = $stmt->fetchAll();

include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 md:px-10 md:py-12">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Admin</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Gestion des produits</h1>
            <p class="mt-4 font-hatton text-main">
                Ajout, modification et suppression des articles affichés dans le shop.
            </p>
            <a href="admin.php" class="mt-5 inline-block rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                Retour dashboard
            </a>
        </div>

        <div class="mt-8 grid gap-8 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">
                    <?= $productId > 0 ? 'Modifier' : 'Ajouter' ?>
                </p>
                <h2 class="mt-2 font-hatton text-3xl text-main">
                    <?= $productId > 0 ? 'Modifier un produit' : 'Nouveau produit' ?>
                </h2>

                <form action="products.php<?= $productId > 0 ? '?id=' . htmlspecialchars($productId) : '' ?>" method="post" enctype="multipart/form-data" class="mt-6 space-y-4">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id_produit" value="<?= htmlspecialchars($product['id_produit'] ?? 0) ?>">
                    <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">

                    <div>
                        <label for="nom" class="font-hatton text-main">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($product['nom'] ?? '') ?>"
                            class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                    </div>

                    <div>
                        <label for="type_produit" class="font-hatton text-main">Type</label>
                        <input type="text" id="type_produit" name="type_produit" value="<?= htmlspecialchars($product['type_produit'] ?? '') ?>"
                            class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="prix" class="font-hatton text-main">Prix</label>
                            <input type="number" step="0.01" id="prix" name="prix" value="<?= htmlspecialchars($product['prix'] ?? '') ?>"
                                class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                        </div>
                        <div>
                            <label for="stock" class="font-hatton text-main">Stock</label>
                            <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($product['stock'] ?? '') ?>"
                                class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                        </div>
                    </div>

                    <div>
                        <label for="subtitle" class="font-hatton text-main">Petite phrase</label>
                        <input type="text" id="subtitle" name="subtitle" value="<?= htmlspecialchars($product['subtitle'] ?? '') ?>"
                            class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                    </div>

                    <div>
                        <label for="description" class="font-hatton text-main">Description</label>
                        <textarea id="description" name="description" rows="5"
                            class="mt-2 w-full rounded-[24px] border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label for="image" class="font-hatton text-main ">Image</label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                            class="mt-2 w-full rounded-full border border-[#CBB59D] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
                        <?php if (!empty($product['image'])): ?>
                            <div class="mt-3 flex items-center gap-4 rounded-[22px] border border-[#CBB59D] bg-[#EEE6DC] p-3">
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="Image actuelle"
                                    class="h-20 w-20 rounded-[18px] object-cover">
                                <p class="font-hatton text-sm text-main">
                                    Image actuelle<br>
                                    <?= htmlspecialchars($product['image']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-3">
                        <button type="submit" class="rounded-full bg-button px-6 py-3 font-hatton text-main">
                            Enregistrer
                        </button>
                        <a href="products.php" class="rounded-full border border-[#CBB59D] px-6 py-3 font-hatton text-main">
                            Nouveau
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-8 md:px-10">
                <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Produits</p>
                <h2 class="mt-2 font-hatton text-3xl text-main">Articles du shop</h2>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full border-collapse font-hatton text-main">
                        <thead>
                            <tr class="border-b border-[#CBB59D] text-left">
                                <th class="px-4 py-3">Nom</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Prix</th>
                                <th class="px-4 py-3">Stock</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $item): ?>
                                <tr class="border-b border-[#E8E2D9]">
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['nom']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['type_produit']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['prix']) ?> €</td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($item['stock']) ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col gap-2">
                                            <a href="products.php?id=<?= htmlspecialchars($item['id_produit']) ?>"
                                                class="rounded-full bg-button px-4 py-2 text-center font-hatton text-main">
                                                Modifier
                                            </a>
                                            <form action="products.php" method="post" onsubmit="return confirm('Supprimer ce produit ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_produit" value="<?= htmlspecialchars($item['id_produit']) ?>">
                                                <button type="submit" class="rounded-full border border-red-300 px-4 py-2 font-hatton text-red-700">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
