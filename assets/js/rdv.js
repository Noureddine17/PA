const baseUrl = window.APP_BASE_URL || '';
const formulaireRdv = document.getElementById('rdv-form');
const messageRdv = document.getElementById('rdv-message');
const dateRdv = document.getElementById('date');

const resumeSoin = document.getElementById('summary-service');
const resumeExpert = document.getElementById('summary-expert');
const resumeCreneau = document.getElementById('summary-slot');
const resumeDuree = document.getElementById('summary-duration');
const resumePrix = document.getElementById('summary-price');
const resumePaiement = document.getElementById('summary-payment');
const noteValidation = document.getElementById('validation-note');

if (dateRdv.value === '') {
    let aujourdhui = new Date();
    let annee = aujourdhui.getFullYear();
    let mois = String(aujourdhui.getMonth() + 1).padStart(2, '0');
    let jour = String(aujourdhui.getDate()).padStart(2, '0');
    dateRdv.value = annee + '-' + mois + '-' + jour;
}

function formatDate(date) {
    let nouvelleDate = new Date(date + 'T12:00:00');
    return nouvelleDate.toLocaleDateString('fr-FR');
}

function changerResume() {
    let soin = document.querySelector('input[name="service"]:checked');
    let expert = document.querySelector('input[name="expert"]:checked');
    let creneau = document.querySelector('input[name="slot"]:checked');
    let paiement = document.querySelector('input[name="payment_mode"]:checked');

    if (!soin || !paiement) {
        return;
    }

    resumeSoin.textContent = soin.value;
    resumeDuree.textContent = soin.dataset.duration;
    resumePrix.textContent = soin.dataset.price;
    resumeExpert.textContent = expert ? expert.dataset.name : 'Aucun expert';
    resumeCreneau.textContent = creneau ? formatDate(dateRdv.value) + ' • ' + creneau.value : 'Aucun créneau disponible';
    resumePaiement.textContent = paiement.value;

    if (paiement.value === 'Paiement en ligne') {
        noteValidation.textContent = 'Action suivante : ajout du rendez-vous au panier.';
    } else {
        noteValidation.textContent = 'Action suivante : envoi d’un mail de confirmation pour paiement sur place.';
    }
}

function afficherMessage(message, erreur) {
    messageRdv.textContent = message;
    messageRdv.classList.remove('hidden');

    if (erreur) {
        messageRdv.classList.remove('bg-[#DDEEDC]');
        messageRdv.classList.add('bg-red-50', 'text-red-700');
    } else {
        messageRdv.classList.add('bg-[#DDEEDC]');
        messageRdv.classList.remove('bg-red-50', 'text-red-700');
    }
}

function ajouterRdvAuPanier(idRdv) {
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
        subtitle: formatDate(dateRdv.value) + ' à ' + creneau.value + ' avec ' + expert.dataset.name,
        price: Number(soin.dataset.price.replace('€', '').trim()),
        quantity: 1,
        image: baseUrl + '/assets/images/services/droplet.svg',
        rdv_id: idRdv
    });

    localStorage.setItem('kaeskin-cart', JSON.stringify(panier));
}

function reserverRdv() {
    let soin = document.querySelector('input[name="service"]:checked');
    let expert = document.querySelector('input[name="expert"]:checked');
    let creneau = document.querySelector('input[name="slot"]:checked');
    let paiement = document.querySelector('input[name="payment_mode"]:checked');

    if (!soin || !expert || !creneau || !paiement) {
        afficherMessage('Veuillez choisir un créneau disponible.', true);
        return Promise.reject();
    }

    return fetch(baseUrl + '/auth/reserver_rdv.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            service: soin.value,
            expert_id: expert.value,
            date: dateRdv.value,
            heure: creneau.value,
            duree: soin.dataset.duration,
            prix: soin.dataset.price,
            payment_mode: paiement.value
        })
    });
}

function mettreAJourCreneaux() {
    let expert = document.querySelector('input[name="expert"]:checked');

    if (!expert || !dateRdv.value) {
        return;
    }

    fetch(baseUrl + '/auth/creneaux_rdv.php?expert_id=' + encodeURIComponent(expert.value) + '&date=' + encodeURIComponent(dateRdv.value))
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            let creneauxReserves = data.reserved || [];
            let premierDisponible = null;

            document.querySelectorAll('input[name="slot"]').forEach(function(input) {
                let label = input.closest('label');
                let box = label.querySelector('.slot-box');
                let reserve = creneauxReserves.includes(input.value);

                input.disabled = reserve;
                label.classList.toggle('cursor-not-allowed', reserve);
                label.classList.toggle('cursor-pointer', !reserve);
                box.classList.toggle('bg-gray-200', reserve);
                box.classList.toggle('text-gray-400', reserve);
                box.classList.toggle('line-through', reserve);
                box.classList.toggle('opacity-60', reserve);
                box.classList.toggle('pointer-events-none', reserve);
                label.title = reserve ? 'Créneau déjà réservé' : '';
                box.textContent = reserve ? input.value + ' réservé' : input.value;

                if (reserve && input.checked) {
                    input.checked = false;
                }

                if (!reserve && premierDisponible === null) {
                    premierDisponible = input;
                }
            });

            if (!document.querySelector('input[name="slot"]:checked') && premierDisponible) {
                premierDisponible.checked = true;
            }

            if (!premierDisponible) {
                afficherMessage('Tous les créneaux sont déjà réservés pour cet expert à cette date.', true);
            } else {
                messageRdv.classList.add('hidden');
            }

            changerResume();
        });
}

document.querySelectorAll('input[type="radio"]').forEach(function(input) {
    input.addEventListener('change', function() {
        if (input.name === 'expert') {
            mettreAJourCreneaux();
        }

        changerResume();
    });
});

dateRdv.addEventListener('change', function() {
    mettreAJourCreneaux();
    changerResume();
});

formulaireRdv.addEventListener('submit', function(event) {
    event.preventDefault();

    if (estConnecte === false) {
        window.location.href = baseUrl + '/auth/login.php';
        return;
    }

    let paiement = document.querySelector('input[name="payment_mode"]:checked');

    reserverRdv()
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (!data.success) {
                afficherMessage(data.message, true);
                mettreAJourCreneaux();
                return;
            }

            if (paiement.value === 'Paiement en ligne') {
                ajouterRdvAuPanier(data.id_rdv);
                window.location.href = baseUrl + '/pages/panier.php';
            } else {
                afficherMessage(data.message, false);
                mettreAJourCreneaux();
            }
        })
        .catch(function() {
            afficherMessage('La réservation n’a pas pu être enregistrée.', true);
        });
});

mettreAJourCreneaux();
changerResume();
