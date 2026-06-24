<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');
require_once(__DIR__ . '/../includes/product_card.php');

$isAdmin = false;

if (isset($_SESSION['id_user'])) {
    $isAdmin = isCurrentAdmin($pdo);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_product') {
    if (!$isAdmin) {
        redirect('shop.php', 'error', 'Action réservée aux administrateurs.');
    }

    $idProduit = (int) ($_POST['id_produit'] ?? 0);

    if ($idProduit <= 0) {
        redirect('shop.php', 'error', 'Produit introuvable.');
    }

    $stmt = $pdo->prepare('DELETE FROM PRODUIT WHERE id_produit = ?');
    $stmt->execute([$idProduit]);

    redirect('shop.php', 'success', 'Produit supprimé.');
}

include(__DIR__ . '/../headers/header.php');

$search = trim($_GET['search'] ?? '');

$sql = '
    SELECT
        id_produit AS id,
        nom AS name,
        type_produit AS type,
        prix AS price,
        subtitle,
        description,
        image
    FROM PRODUIT
';

if ($search !== '') {
    $sql .= '
        WHERE nom LIKE ?
        OR type_produit LIKE ?
        OR description LIKE ?
    ';
    $sql .= ' ORDER BY id_produit';

    $searchSql = '%' . $search . '%';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchSql, $searchSql, $searchSql]);
} else {
    $sql .= ' ORDER BY id_produit';
    $stmt = $pdo->query($sql);
}

$filteredProducts = $stmt->fetchAll();
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-12">
        <div class="grid gap-8">
            <div class="space-y-8">
                <section class="bg-[#F5F2ED] border border-div rounded-[40px] md:rounded-[56px] p-6 md:p-10 shadow-xl/20">
                    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-hatton text-sm uppercase tracking-[0.3em]">Catalogue</p>
                            <h2 class="font-hatton text-3xl text-main">Produits disponibles</h2>
                        </div>
                        <?php displayAlert(); ?>
                        <div class="w-full md:max-w-md">
                            <input type="search" id="shop-search" name="search" value="<?= htmlspecialchars($search) ?>"
                                placeholder="Rechercher un produit"
                                class="w-full rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-3 font-hatton text-main placeholder:text-[#B7A28D] focus:outline-none focus:ring-2 focus:ring-[#B09882]/40">
                        </div>
                    </div>

                    <div id="product-list" class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($filteredProducts as $index => $product): ?>
                            <?php afficherCarteProduit($product, $isAdmin); ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($filteredProducts)): ?>
                        <p id="search-empty" class="mt-6 rounded-[28px] bg-default p-5 text-center font-hatton text-main">
                            Aucun produit trouvé.
                        </p>
                    <?php else: ?>
                        <p id="search-empty" class="mt-6 hidden rounded-[28px] bg-default p-5 text-center font-hatton text-main">
                            Aucun produit trouvé.
                        </p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </section>
</main>

<script>
    const CART_STORAGE_KEY = 'kaeskin-cart';
    const searchInput = document.getElementById('shop-search');
    const productList = document.getElementById('product-list');
    const searchEmpty = document.getElementById('search-empty');

    function getCart() {
        try {
            const storedCart = localStorage.getItem(CART_STORAGE_KEY);
            const parsedCart = storedCart ? JSON.parse(storedCart) : [];
            return Array.isArray(parsedCart) ? parsedCart : [];
        } catch (error) {
            return [];
        }
    }

    function saveCart(cart) {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    }

    function addProductToCart(button) {
        const article = button.closest('article');
        if (!article) return;

        const productQtyInput = article.querySelector('.product-qty');
        const cart = getCart();
        const quantity = Math.max(1, Number(productQtyInput.value));

        const product = {
            id: 'prod_' + button.dataset.id,
            product_id: button.dataset.id,
            name: button.dataset.name,
            type: button.dataset.type,
            price: Number(button.dataset.price),
            subtitle: button.dataset.subtitle,
            quantity: quantity,
            image: button.dataset.image
        };

        const existingItemIndex = cart.findIndex(item => item.id === product.id);

        if (existingItemIndex >= 0) {
            cart[existingItemIndex].quantity += product.quantity;
        } else {
            cart.push(product);
        }

        saveCart(cart);
        button.textContent = 'Ajouté !';
        setTimeout(() => {
            button.textContent = 'Ajouter au panier';
        }, 2000);
    }

    function chercherProduits() {
        const search = searchInput.value;

        fetch('../auth/recherche_shop.php?search=' + encodeURIComponent(search))
            .then(response => response.text())
            .then(html => {
                productList.innerHTML = html;
                searchEmpty.classList.toggle('hidden', html.trim() !== '');
            });
    }

    productList.addEventListener('click', function(event) {
        const button = event.target.closest('.add-product');
        if (button) {
            addProductToCart(button);
        }
    });

    searchInput.addEventListener('input', chercherProduits);
</script>
<?php
include(__DIR__ . '/../headers/footer.php');
?>
