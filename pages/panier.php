<?php
include(__DIR__ . '/../headers/header.php');

// Gérer l'annulation d'un RDV (action serveur)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'annuler_rdv') {
    if (isset($_SESSION['id_user'])) {
        $rdvId = (int)($_POST['rdv_id'] ?? 0);
        $idClient = (int)$_SESSION['id_user'];

        if ($rdvId > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM RENDEZ_VOUS WHERE id_rdv = ? AND id_client = ?');
                $stmt->execute([$rdvId, $idClient]);

                if ($stmt->rowCount() > 0) {
                    // On retire le RDV de la session et on redirige pour rafraîchir
                    unset($_SESSION['panier']['rdv_' . $rdvId]); // Clé exacte utilisée lors de l'ajout
                    redirect('panier.php', 'success', 'Rendez-vous annulé et créneau libéré.'); // La redirection va recharger la page
                } else {
                    redirect('panier.php', 'error', 'Impossible de trouver ou de supprimer ce rendez-vous.');
                }
            } catch (PDOException $e) {
                error_log('Erreur de suppression de RDV: ' . $e->getMessage());
                redirect('panier.php', 'error', 'Erreur du serveur lors de la suppression.');
            }
        }
    } else {
        redirect('../auth/login.php', 'error', 'Vous devez être connecté.');
    }
}

// Récupérer uniquement les rendez-vous de la session pour les passer au JS
$appointmentsFromSession = [];
if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        if (isset($item['type']) && $item['type'] === 'Rendez-vous') {
            $appointmentsFromSession[] = $item;
        }
    }
}
?>

<main class="pb-20">

    <section id="cart-section" class="container mx-auto px-4 pt-12" style="visibility: hidden;">
        <?php displayAlert(); ?>
        <div class="grid gap-8 xl:grid-cols-[1.35fr_0.65fr]">
            <section class="bg-[#F5F2ED] border border-div rounded-[40px] md:rounded-[56px] p-6 md:p-10 shadow-xl/20">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
                    <div>
                        <p class="font-hatton text-sm uppercase tracking-[0.3em]">Récapitulatif</p>
                        <h2 class="font-hatton text-3xl text-main">Articles ajoutés au panier</h2>
                    </div>
                    <a href="shop.php"
                        class="inline-flex items-center justify-center rounded-full bg-button px-6 py-3 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">
                        Continuer mes achats
                    </a>
                </div>

                <div id="cart-items" class="space-y-5">
                    <!-- Le contenu du panier sera généré ici par JavaScript -->
                </div>
                <div id="cart-empty" class="hidden rounded-[32px] border border-dashed border-div bg-default p-8 text-center shadow-xl/10">
                    <p class="font-hatton text-sm uppercase tracking-[0.3em] mb-3">Panier vide</p>
                    <h3 class="font-hatton text-3xl text-main mb-4">Aucun article pour le moment</h3>
                    <p class="font-hatton leading-relaxed max-w-xl mx-auto mb-6">
                        Parcourez la boutique et ajoutez vos essentiels soin et cosmétique pour retrouver votre
                        sélection ici.
                    </p>
                    <a href="shop.php" class="inline-flex items-center justify-center rounded-full bg-button px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882]">
                        Découvrir la boutique
                    </a>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="bg-div rounded-[40px] p-6 md:p-8 shadow-xl/20">
                    <p class="font-hatton text-sm uppercase tracking-[0.3em] mb-4">Total</p>
                    <h2 class="font-hatton text-3xl text-main mb-6">Résumé commande</h2>

                    <div class="space-y-4">
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-sm mb-1">Articles</p>
                            <p class="font-hatton text-2xl text-main" id="summary-items">0</p>
                        </div>
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-sm mb-1">Sous-total</p>
                            <p class="font-hatton text-2xl text-main" id="summary-subtotal">0,00 €</p>
                        </div>
                        <div class="rounded-[28px] bg-[#E8E2D9] p-5">
                            <p class="font-hatton text-sm mb-1">Livraison</p>
                            <p class="font-hatton text-2xl text-main">Offerte</p>
                        </div>

                    </div>

                    <div class="mt-6 rounded-[28px] border border-div p-5">
                        <div class="flex items-center justify-between">
                            <span class="font-hatton text-main">Total estimé</span>
                            <span class="font-hatton text-main text-2xl" id="summary-total">0,00 €</span>
                        </div>
                    </div>

                    <button type="button" id="checkout-button"
                        class="w-full rounded-full bg-button px-6 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100"
                        disabled>
                        Continuer vers le paiement
                    </button>
                </div>


            </aside>
        </div>
    </section>
</main>

<script>
	const appointmentsFromSession = <?= json_encode($appointmentsFromSession) ?>;
	const CART_STORAGE_KEY = 'kaeskin-cart';

	const cartSection = document.getElementById('cart-section');
	const cartItemsContainer = document.getElementById('cart-items');
	const cartEmptyState = document.getElementById('cart-empty');
	const summaryItems = document.getElementById('summary-items');
	const summarySubtotal = document.getElementById('summary-subtotal');
	const summaryTotal = document.getElementById('summary-total');
	const checkoutButton = document.getElementById('checkout-button');

	function formatPrice(value) {
		return `${Number(value).toFixed(2).replace('.', ',')} €`;
	}

	function getProductCart() {
		try {
			const storedCart = localStorage.getItem(CART_STORAGE_KEY);
			return storedCart ? JSON.parse(storedCart) : [];
		} catch (e) {
			console.error("Error parsing cart from localStorage", e);
			return [];
		}
	}

	function saveProductCart(cart) {
		localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
	}

	function removeProductByIndex(indexInFullCart) {
		const productCart = getProductCart();
		const appointmentsCount = appointmentsFromSession.length;
		const indexInProductCart = indexInFullCart - appointmentsCount;

		if (indexInProductCart >= 0 && indexInProductCart < productCart.length) {
			productCart.splice(indexInProductCart, 1);
			saveProductCart(productCart);
			renderCart();
		} else {
			console.error("Could not remove product, index out of bounds.");
		}
	}

	function createCartItemHTML(item, index) {
		const article = document.createElement('article');
		article.className = 'rounded-[32px] border border-div bg-default p-5 shadow-xl/10';
		const itemSubtotal = item.price * item.quantity;

		let removeButtonHTML;
		if (item.type === 'Rendez-vous') {
			removeButtonHTML = `
                <form method="POST" action="panier.php" class="self-start">
                    <input type="hidden" name="action" value="annuler_rdv">
                    <input type="hidden" name="rdv_id" value="${item.rdv_id}">
                    <button type="submit" class="rounded-full border border-div px-5 py-3 font-hatton text-main transition-all duration-300 hover:scale-105 hover:bg-[#E8E2D9]">
                        Supprimer
                    </button>
                </form>
            `;
		} else {
			removeButtonHTML = `
                <button type="button" class="remove-product-item self-start rounded-full border border-div px-5 py-3 font-hatton text-main transition-all duration-300 hover:scale-105 hover:bg-[#E8E2D9]" data-index="${index}">
                    Supprimer
                </button>
            `;
		}

		article.innerHTML = `
            <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                <div class="flex flex-col gap-5 md:flex-row md:items-start">
                    <div class="h-36 w-full overflow-hidden rounded-[24px] bg-[#E8E2D9] md:w-32 md:min-w-32">
                        <img src="${item.image || ''}" alt="${item.name}" class="h-full w-full object-cover">
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="font-hatton text-sm uppercase tracking-[0.25em]">${item.type}</p>
                            <h3 class="font-hatton text-3xl text-main">${item.name}</h3>
                        </div>
                        <p class="font-hatton leading-relaxed max-w-2xl">${item.subtitle || ''}</p>
                        <div class="flex flex-wrap gap-3">
                            <div class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main">
                                Quantité : ${item.quantity}
                            </div>
                            <div class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main">
                                Prix unitaire : ${formatPrice(item.price)}
                            </div>
                            <div class="rounded-full bg-[#E8E2D9] px-4 py-2 font-hatton text-main">
                                Sous-total : ${formatPrice(itemSubtotal)}
                            </div>
                        </div>
                    </div>
                </div>
                ${removeButtonHTML}
            </div>
        `;
		return article;
	}

	function renderCart() {
		const productCart = getProductCart();
		const fullCart = [...appointmentsFromSession, ...productCart];

		cartItemsContainer.innerHTML = '';

		if (fullCart.length === 0) {
			cartEmptyState.classList.remove('hidden');
		} else {
			cartEmptyState.classList.add('hidden');
			fullCart.forEach((item, index) => {
				cartItemsContainer.appendChild(createCartItemHTML(item, index));
			});
		}

		const totalItems = fullCart.reduce((sum, item) => sum + item.quantity, 0);
		const subtotal = fullCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

		summaryItems.textContent = totalItems;
		summarySubtotal.textContent = formatPrice(subtotal);
		summaryTotal.textContent = formatPrice(subtotal);
		checkoutButton.disabled = fullCart.length === 0;
	}

	cartItemsContainer.addEventListener('click', (event) => {
		const removeButton = event.target.closest('.remove-product-item');
		if (removeButton) {
			removeProductByIndex(Number(removeButton.dataset.index));
		}
	});

	checkoutButton.addEventListener('click', () => {
		if (!checkoutButton.disabled) {
			window.location.href = 'paiement.php';
		}
	});

	document.addEventListener('DOMContentLoaded', () => {
		renderCart();
		cartSection.style.visibility = 'visible';
	});
</script>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
