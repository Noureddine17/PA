<?php
include('headers/header.php');
?>

<main class="pb-20">


    <section class="container mx-auto px-4 pt-12">
        <div class="grid gap-8 xl:grid-cols-[1.35fr_0.65fr]">
            <section class="bg-[#F5F2ED] border border-div rounded-[40px] md:rounded-[56px] p-6 md:p-10 shadow-xl/20">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
                    <div>
                        <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main/70">Récapitulatif</p>
                        <h2 class="font-hatton text-3xl text-main">Articles ajoutés au panier</h2>
                    </div>
                    <a href="shop.php"
                        class="inline-flex items-center justify-center rounded-full bg-button px-6 py-3 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">
                        Continuer mes achats
                    </a>
                </div>

                <div id="cart-items" class="space-y-5"></div>

                <div id="cart-empty"
                    class="hidden rounded-[32px] border border-dashed border-div bg-default p-8 text-center shadow-xl/10">
                    <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main/70 mb-3">Panier vide</p>
                    <h3 class="font-hatton text-3xl text-main mb-4">Aucun article pour le moment</h3>
                    <p class="font-hatton text-main/80 leading-relaxed max-w-xl mx-auto mb-6">
                        Parcourez la boutique et ajoutez vos essentiels soin et cosmétique pour retrouver votre
                        sélection ici.
                    </p>
                    <a href="shop.php"
                        class="inline-flex items-center justify-center rounded-full bg-button px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">
                        Découvrir la boutique
                    </a>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="bg-div rounded-[40px] p-6 md:p-8 shadow-xl/20">
                    <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main/70 mb-4">Total</p>
                    <h2 class="font-hatton text-3xl text-main mb-6">Résumé commande</h2>

                    <div class="space-y-4">
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-main/70 text-sm mb-1">Articles</p>
                            <p class="font-hatton text-2xl text-main" id="summary-items">0</p>
                        </div>
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-main/70 text-sm mb-1">Sous-total</p>
                            <p class="font-hatton text-2xl text-main" id="summary-subtotal">0 €</p>
                        </div>
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-main/70 text-sm mb-1">Livraison</p>
                            <p class="font-hatton text-2xl text-main">Offerte</p>
                        </div>

                    </div>

                    <div class="mt-6 rounded-[28px] border border-div p-5">
                        <div class="flex items-center justify-between">
                            <span class="font-hatton text-main">Total estimé</span>
                            <span class="font-hatton text-main text-2xl" id="summary-total">0 €</span>
                        </div>
                    </div>

                    <button type="button" id="checkout-button"
                        class="w-full rounded-full bg-button px-6 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100">
                        Continuer vers le paiement
                    </button>
                </div>


            </aside>
        </div>
    </section>
</main>

<script>
    const CART_STORAGE_KEY = 'kaeskin-cart';
    const cartItemsContainer = document.getElementById('cart-items');
    const cartEmptyState = document.getElementById('cart-empty');
    const summaryItems = document.getElementById('summary-items');
    const summarySubtotal = document.getElementById('summary-subtotal');
    const summaryTotal = document.getElementById('summary-total');
    const checkoutButton = document.getElementById('checkout-button');

    function formatPrice(value) {
        return `${value} €`;
    }

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

    function removeItem(index) {
        const cart = getCart();
        cart.splice(index, 1);
        saveCart(cart);
        renderCartPage();
    }

    function createCartItem(item, index) {
        const article = document.createElement('article');
        article.className = 'rounded-[32px] border border-div bg-default p-5 shadow-xl/10';

        const subtotal = item.price * item.quantity;

        article.innerHTML = `
            <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                <div class="flex flex-col gap-5 md:flex-row md:items-start">
                    <div class="h-36 w-full overflow-hidden rounded-[24px] bg-[#E8E2D9] md:w-32 md:min-w-32">
                        <img src="${item.image || ''}" alt="${item.name}"
                            class="h-full w-full object-cover">
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="font-hatton text-main/70 text-sm uppercase tracking-[0.25em]">${item.type}</p>
                            <h3 class="font-hatton text-3xl text-main">${item.name}</h3>
                        </div>
                        <p class="font-hatton text-main/80 leading-relaxed max-w-2xl">${item.subtitle}</p>
                        <div class="flex flex-wrap gap-3">
                            <div class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main">
                                Quantité : ${item.quantity}
                            </div>
                            <div class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main">
                                Prix unitaire : ${formatPrice(item.price)}
                            </div>
                            <div class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main">
                                Sous-total : ${formatPrice(subtotal)}
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button"
                    class="remove-item self-start rounded-full border border-div px-5 py-3 font-hatton text-main transition-all duration-300 hover:scale-105 hover:bg-[#E8E2D9]"
                    data-index="${index}">
                    Supprimer
                </button>
            </div>
        `;

        return article;
    }

    function renderCartPage() {
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        cartItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            cartEmptyState.classList.remove('hidden');
        } else {
            cartEmptyState.classList.add('hidden');
            cart.forEach((item, index) => {
                cartItemsContainer.appendChild(createCartItem(item, index));
            });
        }

        summaryItems.textContent = String(totalItems);
        summarySubtotal.textContent = formatPrice(subtotal);
        summaryTotal.textContent = formatPrice(subtotal);
        checkoutButton.disabled = cart.length === 0;
    }

    cartItemsContainer.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-item');
        if (!removeButton) {
            return;
        }

        removeItem(Number(removeButton.dataset.index));
    });

    checkoutButton.addEventListener('click', () => {
        if (checkoutButton.disabled) {
            return;
        }

        window.location.href = 'paiement.php';
    });

    renderCartPage();
</script>

<?php
include('headers/footer.php');
?>
