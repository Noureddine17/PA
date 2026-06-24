<?php
function afficherCarteProduit($product, $isAdmin = false)
{
    ?>
    <article class="rounded-[32px] border border-div bg-default p-5 shadow-xl/10">
        <div class="rounded-[26px] bg-[#E8E2D9] mb-5 aspect-[4/3]">
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
            <p class="mt-3 font-hatton leading-relaxed">
                <?= htmlspecialchars($product['description']) ?>
            </p>
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <label class="block font-hatton text-main mb-2">Quantité</label>
                <input type="number" min="1" value="1"
                    class="product-qty w-28 rounded-full border border-[#D4C0AB] bg-[#EEE6DC] px-5 py-3 font-hatton text-main">
            </div>
            <div class="flex flex-col items-start gap-3 sm:items-end">
                <span class="font-hatton text-2xl text-main"><?= (float) $product['price'] ?> €</span>
                <button type="button"
                    class="add-product rounded-full bg-button px-5 py-3 font-hatton text-main transition-all duration-300 hover:scale-105"
                    data-id="<?= htmlspecialchars($product['id']) ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-type="<?= htmlspecialchars($product['type']) ?>"
                    data-price="<?= htmlspecialchars($product['price']) ?>"
                    data-subtitle="<?= htmlspecialchars($product['subtitle']) ?>"
                    data-image="<?= htmlspecialchars($product['image']) ?>">
                    Ajouter au panier
                </button>
                <?php if ($isAdmin): ?>
                    <form action="../pages/shop.php" method="post" onsubmit="return confirm('Supprimer ce produit ?');">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="id_produit" value="<?= htmlspecialchars($product['id']) ?>">
                        <button type="submit" class="rounded-full border border-red-300 px-5 py-3 font-hatton text-red-700">
                            Supprimer
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}
