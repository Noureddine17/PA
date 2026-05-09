// Elements de la page
const baseUrl = window.APP_BASE_URL || '';
const formulaireRdv = document.getElementById('rdv-form');
const messageRdv = document.getElementById('rdv-message');
const dateRdv = document.getElementById('date');

// Resume a droite
const resumeSoin = document.getElementById('summary-service');
const resumeExpert = document.getElementById('summary-expert');
const resumeCreneau = document.getElementById('summary-slot');
const resumeDuree = document.getElementById('summary-duration');
const resumePrix = document.getElementById('summary-price');
const resumePaiement = document.getElementById('summary-payment');
const noteValidation = document.getElementById('validation-note');

// Date par defaut
if (dateRdv.value === '') {
    let aujourdhui = new Date();
    let annee = aujourdhui.getFullYear();
    let mois = String(aujourdhui.getMonth() + 1).padStart(2, '0');
    let jour = String(aujourdhui.getDate()).padStart(2, '0');
    dateRdv.value = annee + '-' + mois + '-' + jour;
}

// Format de la date pour l'affichage
function formatDate(date) {
    let nouvelleDate = new Date(date + 'T12:00:00');
    return nouvelleDate.toLocaleDateString('fr-FR');
}

// Mise a jour du resume
function changerResume() {
    let soin = document.querySelector('input[name="service"]:checked');
    let expert = document.querySelector('input[name="expert"]:checked');
    let creneau = document.querySelector('input[name="slot"]:checked');
    let paiement = document.querySelector('input[name="payment_mode"]:checked');

    resumeSoin.textContent = soin.value;
    resumeDuree.textContent = soin.dataset.duration;
    resumePrix.textContent = soin.dataset.price;
    resumeExpert.textContent = expert.value;
    resumeCreneau.textContent = formatDate(dateRdv.value) + ' • ' + creneau.value;
    resumePaiement.textContent = paiement.value;

    if (paiement.value === 'Paiement en ligne') {
        noteValidation.textContent = 'Action suivante : ajout du rendez-vous au panier.';
    } else {
        noteValidation.textContent = 'Action suivante : envoi d’un mail de confirmation pour paiement sur place.';
    }
}

// Ajout du rdv au panier
function ajouterRdvAuPanier() {
    let soin = document.querySelector('input[name="service"]:checked');
    let expert = document.querySelector('input[name="expert"]:checked');
    let creneau = document.querySelector('input[name="slot"]:checked');

    let panier = localStorage.getItem('kaeskin-cart');

    if (panier) {
        panier = JSON.parse(panier);
    } else {
        panier = [];
    }

    panier.push({
        name: soin.value,
        type: 'Rendez-vous',
        subtitle: formatDate(dateRdv.value) + ' à ' + creneau.value + ' avec ' + expert.value,
        price: Number(soin.dataset.price.replace('€', '').trim()),
        quantity: 1,
        image: baseUrl + '/assets/images/services/droplet.svg'
    });

    localStorage.setItem('kaeskin-cart', JSON.stringify(panier));
}

function envoyerMailRdv() {
    let soin = document.querySelector('input[name="service"]:checked');
    let expert = document.querySelector('input[name="expert"]:checked');
    let creneau = document.querySelector('input[name="slot"]:checked');

    return fetch(baseUrl + '/auth/envoyer_rdv.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            service: soin.value,
            expert: expert.value,
            date: formatDate(dateRdv.value),
            heure: creneau.value,
            duree: soin.dataset.duration,
            prix: soin.dataset.price
        })
    });
}

// Quand un choix change
document.querySelectorAll('input[type="radio"]').forEach(function(input) {
    input.addEventListener('change', changerResume);
});

dateRdv.addEventListener('change', changerResume);

// Validation du formulaire
formulaireRdv.addEventListener('submit', function(event) {
    event.preventDefault();

    if (estConnecte === false) {
        window.location.href = baseUrl + '/auth/login.php';
        return;
    }

    let paiement = document.querySelector('input[name="payment_mode"]:checked');

    if (paiement.value === 'Paiement en ligne') {
        ajouterRdvAuPanier();
        window.location.href = baseUrl + '/pages/panier.php';
    } else {
        envoyerMailRdv()
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                messageRdv.textContent = data.message;
                messageRdv.classList.remove('hidden');
            });
    }
});

changerResume();