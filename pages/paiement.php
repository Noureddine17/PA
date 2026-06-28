<?php
include(__DIR__ . '/../headers/header.php');

requireLogin();

$total = $_GET['total'] ?? '0';
$totalFloat = floatval($total);
$totalFormatted = number_format($totalFloat, 2, ',', ' ') . ' €';
?>

<main class="pb-20">
    <section class="container mx-auto px-4 pt-12">
        <div class="bg-div rounded-[48px] md:rounded-[72px] shadow-xl/20 px-6 py-10 md:px-12 md:py-14">
            <div class="max-w-3xl mx-auto">
                <p class="font-hatton text-sm uppercase tracking-[0.35em] mb-4 text-center">Paiement sécurisé</p>
                <h1 class="font-hatton text-main text-4xl md:text-5xl leading-tight mb-5 text-center">
                    Réglez votre commande
                </h1>
                <p class="text-main font-hatton text-lg leading-relaxed mb-8 text-center">
                    Le montant total de votre commande est de
                    <span class="font-bold text-2xl"><?= htmlspecialchars($totalFormatted) ?></span>.
                </p>

                <form id="payment-form" class="space-y-6">
                    <div>
                        <label for="card-element" class="font-hatton text-main text-lg mb-2 block">
                            Informations de paiement
                        </label>
                        <div id="card-element" class="rounded-full bg-default border border-div px-6 py-4 shadow-inner"></div>
                        <div id="card-errors" role="alert" class="text-red-500 font-hatton text-sm mt-2"></div>
                    </div>

                    <div class="flex flex-wrap gap-4 pt-4">
                        <button type="submit" id="submit-button"
                            class="w-full rounded-full bg-button px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:!bg-[#E8E2D9] hover:!text-[#B09882] disabled:opacity-60">
                            <span id="button-text">Payer <?= htmlspecialchars($totalFormatted) ?></span>
                            <span id="spinner" class="hidden">Processing...</span>
                        </button>
                        <a href="panier.php"
                            class="w-full text-center rounded-full border border-div bg-transparent px-8 py-4 font-hatton text-main transition-all duration-300 hover:scale-105 hover:bg-[#E8E2D9]">
                            Retour au panier
                        </a>
                    </div>
                </form>
                <div id="payment-success" class="hidden mt-8 text-center p-6 bg-green-100 rounded-2xl">
                    <h3 class="font-hatton text-2xl text-green-800">Paiement réussi !</h3>
                    <p class="font-hatton mt-2">Votre commande a été traitée avec succès. (Simulation)</p>
                </div>

            </div>
        </div>
    </section>
</main>

<script src="https://js.stripe.com/v3/"></script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const stripe = Stripe('pk_test_51TmYowHHncBXtuj2RK7o8p4qvMMn3f4s4IuhBX3BAbwOedUE9bIUk6wgyi9BG42H7acf1w9tBTyPnhZDlczriEJV00UF7GRqpe');

		const elements = stripe.elements({
			locale: 'fr',
			fonts: [{
				cssSrc: 'https://fonts.googleapis.com/css?family=Lexend:400,700',
			}, ],
		});

		const style = {
			base: {
				color: '#32325d',
				fontFamily: '"Lexend", sans-serif',
				fontSmoothing: 'antialiased',
				fontSize: '16px',
				'::placeholder': {
					color: '#aab7c4'
				}
			},
			invalid: {
				color: '#fa755a',
				iconColor: '#fa755a'
			}
		};

		const card = elements.create('card', {
			style: style,
			hidePostalCode: true
		});
		card.mount('#card-element');

		card.addEventListener('change', function(event) {
			const displayError = document.getElementById('card-errors');
			if (event.error) {
				displayError.textContent = event.error.message;
			} else {
				displayError.textContent = '';
			}
		});

		const form = document.getElementById('payment-form');
		const submitButton = document.getElementById('submit-button');
		const buttonText = document.getElementById('button-text');
		const spinner = document.getElementById('spinner');
		const paymentSuccessMessage = document.getElementById('payment-success');

		form.addEventListener('submit', async function(event) {
			event.preventDefault();
			submitButton.disabled = true;
			buttonText.classList.add('hidden');
			spinner.classList.remove('hidden');

			setTimeout(() => {
				submitButton.disabled = false;
				buttonText.classList.remove('hidden');
				spinner.classList.add('hidden');

				form.classList.add('hidden');
				paymentSuccessMessage.classList.remove('hidden');

				localStorage.removeItem('kaeskin-cart');


			}, 2000);
		});
	});
</script>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
