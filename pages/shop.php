<?php
include(__DIR__ . '/../headers/header.php');

$productCatalog = [
    [
        'name' => 'Velvet Cream',
        'type' => 'Crème hydratante',
        'price' => 36,
        'subtitle' => 'Hydratation souple et fini velouté.',
        'description' => 'Une crème confortable pour nourrir la peau au quotidien et lui redonner douceur et équilibre.',
        'benefits' => 'Confort immédiat, peau souple, routine quotidienne.',
        'usage' => 'Appliquer matin et soir sur peau propre.',
        'badge' => 'Iconique',
        'image' => '../assets/images/shop/hd-cream.jpg',
    ],
    [
        'name' => 'Silk Cleanser',
        'type' => 'Gel nettoyant',
        'price' => 28,
        'subtitle' => 'Nettoyage doux pour une peau fraîche et nette.',
        'description' => 'Ce nettoyant élimine les impuretés sans agresser la peau et prépare idéalement la suite de la routine.',
        'benefits' => 'Peau nette, toucher doux, confort après rinçage.',
        'usage' => 'Masser sur peau humide puis rincer à l’eau tiède.',
        'badge' => 'Essentiel',
        'image' => '../assets/images/shop/hd-cleanser.jpg',
    ],
    [
        'name' => 'Glow Ritual',
        'type' => 'Soin éclat',
        'price' => 42,
        'subtitle' => 'Un soin lumineux pour raviver le teint.',
        'description' => 'Formulé pour réveiller l’éclat naturel de la peau, ce soin accompagne les teints ternes et fatigués.',
        'benefits' => 'Teint plus lumineux, grain de peau visuellement lissé.',
        'usage' => 'Appliquer en fine couche avant la crème de jour.',
        'badge' => 'Best-seller',
        'image' => '../assets/images/shop/hd-glow.jpg',
    ],
    [
        'name' => 'Pure Balance',
        'type' => 'Sérum équilibrant',
        'price' => 39,
        'subtitle' => 'Texture légère pour une peau plus harmonieuse.',
        'description' => 'Un sérum pensé pour aider à équilibrer la peau et apporter une sensation de fraîcheur durable.',
        'benefits' => 'Équilibre, confort, fini léger.',
        'usage' => 'Déposer quelques gouttes avant votre crème.',
        'badge' => 'Routine jour',
        'image' => '../assets/images/shop/hd-balance.jpg',
    ],
    [
        'name' => 'Soft Veil',
        'type' => 'Crème cocon',
        'price' => 44,
        'subtitle' => 'Une formule enveloppante pour les peaux en quête de confort.',
        'description' => 'Sa texture riche procure un effet cocon et aide la peau à conserver sa souplesse tout au long de la journée.',
        'benefits' => 'Nutrition, souplesse, sensation apaisante.',
        'usage' => 'Appliquer sur le visage et le cou selon les besoins.',
        'badge' => 'Peaux sèches',
        'image' => '../assets/images/shop/hd-soft-veil.jpg',
    ],
    [
        'name' => 'Zen Drop',
        'type' => 'Huile soin',
        'price' => 48,
        'subtitle' => 'Un rituel nourrissant pour terminer la routine.',
        'description' => 'Cette huile soin apporte une sensation de confort et laisse la peau plus souple au réveil.',
        'benefits' => 'Nutrition, éclat, confort nocturne.',
        'usage' => 'Réchauffer quelques gouttes entre les mains puis masser.',
        'badge' => 'Rituel nuit',
        'image' => '../assets/images/shop/hd-zen-drop.jpg',
    ],
];

$selectedProduct = $productCatalog[0];
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-12">
        <div class="grid gap-8">
            <div class="space-y-8">
                <section class="bg-[#F5F2ED] border border-div rounded-[40px] md:rounded-[56px] p-6 md:p-10 shadow-xl/20">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
                        <div>
                            <p class="font-hatton text-sm uppercase tracking-[0.3em]">Catalogue</p>
                            <h2 class="font-hatton text-3xl text-main">Produits disponibles</h2>
                        </div>
                      
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <?php foreach ($productCatalog as $product): ?>
                            <article class="rounded-[32px] border border-div bg-default p-5 shadow-xl/10">
                                <div class="rounded-[26px] bg-[#E8E2D9]  mb-5 aspect-[4/3]">
                                    <img src="<?= htmlspecialchars($product['image']) ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="h-full w-full object-cover">
                                </div>
                                <div class="mb-5">
                                    <p class="font-hatton text-sm uppercase tracking-[0.25em]">
                                        <?= htmlspecialchars($product['type']) ?>
                                    </p>
                                    <h3 class="font-hatton text-3xl text-main">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h3>
                                </div>
                                <p class="font-hatton leading-relaxed mb-5">
                                    <?= htmlspecialchars($product['subtitle']) ?>
                                </p>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="font-hatton text-2xl text-main"><?= $product['price'] ?> €</span>
                                    <button type="button"
                                        class="product-trigger rounded-full bg-button px-5 py-3 font-hatton text-main transition-all duration-300 hover:scale-105"
                                        data-name="<?= htmlspecialchars($product['name']) ?>"
                                        data-type="<?= htmlspecialchars($product['type']) ?>"
                                        data-price="<?= $product['price'] ?> €"
                                        data-subtitle="<?= htmlspecialchars($product['subtitle']) ?>"
                                        data-description="<?= htmlspecialchars($product['description']) ?>"
                                        data-benefits="<?= htmlspecialchars($product['benefits']) ?>"
                                        data-usage="<?= htmlspecialchars($product['usage']) ?>"
                                        data-badge="<?= htmlspecialchars($product['badge']) ?>"
                                        data-image="<?= htmlspecialchars($product['image']) ?>">
                                        Voir
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="bg-div rounded-[40px] md:rounded-[56px] p-6 md:p-10 shadow-xl/20">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between mb-8">
                        <div>
                            <p class="font-hatton text-sm uppercase tracking-[0.3em]">Fiche produit</p>
                            <h2 class="font-hatton text-3xl text-main" id="product-name">
                                <?= htmlspecialchars($selectedProduct['name']) ?>
                            </h2>
                            <p class="font-hatton mt-2 text-lg" id="product-subtitle">
                                <?= htmlspecialchars($selectedProduct['subtitle']) ?>
                            </p>
                        </div>
                        <span class="rounded-full bg-[#E8E2D9] px-5 py-2 font-hatton text-main" id="product-badge">
                            <?= htmlspecialchars($selectedProduct['badge']) ?>
                        </span>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                        <div class="space-y-5">
                            <div class="rounded-[32px] bg-[#E8E2D9] overflow-hidden aspect-[4/5]">
                                <img src="<?= htmlspecialchars($selectedProduct['image']) ?>"
                                    alt="<?= htmlspecialchars($selectedProduct['name']) ?>"
                                    id="product-image"
                                    class="h-full w-full object-cover">
                            </div>
                            <div class="rounded-[32px] bg-[#E8E2D9] min-h-[120px] p-6 flex flex-col justify-end">
                                <p class="font-hatton uppercase tracking-[0.25em] text-sm mb-2" id="product-type">
                                    <?= htmlspecialchars($selectedProduct['type']) ?>
                                </p>
                                <p class="font-hatton text-4xl text-main" id="product-price">
                                    <?= $selectedProduct['price'] ?> €
                                </p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                                <p class="font-hatton text-sm uppercase tracking-[0.25em] mb-2">Description</p>
                                <p class="font-hatton text-main leading-relaxed" id="product-description">
                                    <?= htmlspecialchars($selectedProduct['description']) ?>
                                </p>
                            </div>
                            <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                                <p class="font-hatton text-sm uppercase tracking-[0.25em] mb-2">Bénéfices</p>
                                <p class="font-hatton text-main leading-relaxed" id="product-benefits">
                                    <?= htmlspecialchars($selectedProduct['benefits']) ?>
                                </p>
                            </div>
                            <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                                <p class="font-hatton text-sm uppercase tracking-[0.25em] mb-2">Utilisation</p>
                                <p class="font-hatton text-main leading-relaxed" id="product-usage">
                                    <?= htmlspecialchars($selectedProduct['usage']) ?>
                                </p>
                            </div>
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <button type="button" id="decrease-qty"
                                        class="h-12 w-12 rounded-full border border-[#E8E2D9] bg-transparent font-hatton text-main transition-colors hover:bg-[#E8E2D9]">-</button>
                                    <span class="font-hatton text-2xl text-main min-w-[2ch] text-center" id="product-qty">1</span>
                                    <button type="button" id="increase-qty"
                                        class="h-12 w-12 rounded-full border border-[#E8E2D9] bg-transparent font-hatton text-main transition-colors hover:bg-[#E8E2D9]">+</button>
                                </div>
                                <button type="button" id="add-to-cart"
                                    class="rounded-full bg-button px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105">
                                    Ajouter au panier
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</main>

<script>
    const CART_STORAGE_KEY = 'kaeskin-cart';
    const productTriggers = document.querySelectorAll('.product-trigger');
    const productName = document.getElementById('product-name');
    const productType = document.getElementById('product-type');
    const productPrice = document.getElementById('product-price');
    const productSubtitle = document.getElementById('product-subtitle');
    const productDescription = document.getElementById('product-description');
    const productBenefits = document.getElementById('product-benefits');
    const productUsage = document.getElementById('product-usage');
    const productBadge = document.getElementById('product-badge');
    const productImage = document.getElementById('product-image');
    const productQty = document.getElementById('product-qty');

    let selectedProduct = {
        name: '<?= addslashes($selectedProduct['name']) ?>',
        type: '<?= addslashes($selectedProduct['type']) ?>',
        price: <?= (int) $selectedProduct['price'] ?>,
        priceLabel: '<?= (int) $selectedProduct['price'] ?> €',
        subtitle: '<?= addslashes($selectedProduct['subtitle']) ?>',
        description: '<?= addslashes($selectedProduct['description']) ?>',
        benefits: '<?= addslashes($selectedProduct['benefits']) ?>',
        usage: '<?= addslashes($selectedProduct['usage']) ?>',
        badge: '<?= addslashes($selectedProduct['badge']) ?>',
        image: '<?= addslashes($selectedProduct['image']) ?>'
    };

    let quantity = 1;

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

    function renderProduct() {
        productName.textContent = selectedProduct.name;
        productType.textContent = selectedProduct.type;
        productPrice.textContent = selectedProduct.priceLabel;
        productSubtitle.textContent = selectedProduct.subtitle;
        productDescription.textContent = selectedProduct.description;
        productBenefits.textContent = selectedProduct.benefits;
        productUsage.textContent = selectedProduct.usage;
        productBadge.textContent = selectedProduct.badge;
        productImage.src = selectedProduct.image;
        productImage.alt = selectedProduct.name;
        productQty.textContent = String(quantity);
    }

    function addCurrentProductToCart() {
        const cart = getCart();
        const existingItemIndex = cart.findIndex((item) => item.name === selectedProduct.name);

        if (existingItemIndex >= 0) {
            cart[existingItemIndex].quantity += quantity;
            cart[existingItemIndex].subtitle = selectedProduct.subtitle;
            cart[existingItemIndex].type = selectedProduct.type;
            cart[existingItemIndex].price = selectedProduct.price;
            cart[existingItemIndex].image = selectedProduct.image;
        } else {
            cart.push({
                name: selectedProduct.name,
                type: selectedProduct.type,
                price: selectedProduct.price,
                subtitle: selectedProduct.subtitle,
                quantity: quantity,
                image: selectedProduct.image
            });
        }

        saveCart(cart);
    }

    productTriggers.forEach((button) => {
        button.addEventListener('click', () => {
            selectedProduct = {
                name: button.dataset.name,
                type: button.dataset.type,
                price: Number(button.dataset.price.replace(/[^\d]/g, '')),
                priceLabel: button.dataset.price,
                subtitle: button.dataset.subtitle,
                description: button.dataset.description,
                benefits: button.dataset.benefits,
                usage: button.dataset.usage,
                badge: button.dataset.badge,
                image: button.dataset.image
            };
            quantity = 1;
            renderProduct();
        });
    });

    document.getElementById('increase-qty').addEventListener('click', () => {
        quantity += 1;
        productQty.textContent = String(quantity);
    });

    document.getElementById('decrease-qty').addEventListener('click', () => {
        if (quantity > 1) {
            quantity -= 1;
            productQty.textContent = String(quantity);
        }
    });

    document.getElementById('add-to-cart').addEventListener('click', () => {
        addCurrentProductToCart();
    });

    renderProduct();
</script>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
