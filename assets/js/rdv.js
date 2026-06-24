const baseUrl = window.APP_BASE_URL || '';
const formulaireRdv = document.getElementById('rdv-form');
const dateRdv = document.getElementById('date');
const listeCreneaux = document.getElementById('slot-list');

const resumeSoin = document.getElementById('summary-service');
const resumeExpert = document.getElementById('summary-expert');
const resumeCreneau = document.getElementById('summary-slot');
const resumeDuree = document.getElementById('summary-duration');
const resumePrix = document.getElementById('summary-price');
const resumePaiement = document.getElementById('summary-payment');
const noteValidation = document.getElementById('validation-note');
const hiddenDuration = document.getElementById('hidden-duration');
const hiddenPrice = document.getElementById('hidden-price');

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

    if (hiddenDuration) {
        hiddenDuration.value = soin.dataset.duration;
        hiddenPrice.value = soin.dataset.price;
    }
    resumeCreneau.textContent = creneau ? formatDate(dateRdv.value) + ' • ' + creneau.value : 'Aucun créneau disponible';
    resumePaiement.textContent = paiement.value;

    if (paiement.value === 'Paiement en ligne') {
        noteValidation.textContent = 'Action suivante : ajout du rendez-vous au panier.';
    } else {
        noteValidation.textContent = 'Action suivante : envoi d’un mail de confirmation pour paiement sur place.';
        
    }
}

function creerBoutonCreneau(heure) {
    let label = document.createElement('label');
    label.className = 'cursor-pointer';

    let input = document.createElement('input');
    input.type = 'radio';
    input.name = 'slot';
    input.value = heure;
    input.className = 'peer sr-only';

    let box = document.createElement('span');
    box.className = 'slot-box block rounded-[20px] border border-div bg-default px-3 py-4 text-center font-hatton text-main transition-all duration-300 hover:shadow-xl/20 peer-checked:bg-button peer-checked:border-[#8F755E] sm:rounded-[24px] sm:px-4';
    box.textContent = heure;

    label.appendChild(input);
    label.appendChild(box);

    return label;
}

function afficherAucunCreneau(message) {
    listeCreneaux.innerHTML = '';

    let texte = document.createElement('p');
    texte.className = 'col-span-full rounded-[20px] bg-default px-4 py-4 text-center font-hatton text-main';
    texte.textContent = message;

    listeCreneaux.appendChild(texte);
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
            let creneaux = data.slots || [];
            let creneauxReserves = data.reserved || [];
            let creneauxPasses = data.unavailable || [];
            let premierDisponible = null;

            listeCreneaux.innerHTML = '';

            if (creneaux.length === 0) {
                afficherAucunCreneau('Aucun créneau disponible.');
                changerResume();
                return;
            }

            creneaux.forEach(function(creneau) {
                listeCreneaux.appendChild(creerBoutonCreneau(creneau));
            });

            document.querySelectorAll('input[name="slot"]').forEach(function(input) {
                let label = input.closest('label');
                let box = label.querySelector('.slot-box');
                let reserve = creneauxReserves.includes(input.value);
                let passe = creneauxPasses.includes(input.value);
                let bloque = reserve || passe;

                input.disabled = bloque;
                label.classList.toggle('cursor-not-allowed', bloque);
                label.classList.toggle('cursor-pointer', !bloque);
                box.classList.toggle('bg-gray-200', bloque);
                box.classList.toggle('text-gray-400', bloque);
                box.classList.toggle('line-through', bloque);
                box.classList.toggle('opacity-60', bloque);
                box.classList.toggle('pointer-events-none', bloque);
                label.title = reserve ? 'Créneau déjà réservé' : (passe ? 'Créneau déjà passé' : '');

                if (reserve) {
                    box.textContent = input.value + ' réservé';
                } else if (passe) {
                    box.textContent = input.value + ' passé';
                } else {
                    box.textContent = input.value;
                }

                if (bloque && input.checked) {
                    input.checked = false;
                }

                if (!bloque && premierDisponible === null) {
                    premierDisponible = input;
                }
            });

            if (!document.querySelector('input[name="slot"]:checked') && premierDisponible) {
                premierDisponible.checked = true;
            }

            // No more JS messages, handled by PHP reload
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

listeCreneaux.addEventListener('change', changerResume);

dateRdv.addEventListener('change', function() {
    mettreAJourCreneaux();
    changerResume();
});

if (formulaireRdv) {
    formulaireRdv.addEventListener('submit', function(event) {
        // Redirect to login if not connected, but let the form submit otherwise
        if (estConnecte === false) {
            event.preventDefault();
            window.location.href = baseUrl + '/auth/login.php';
        }
    });
    mettreAJourCreneaux();
    changerResume();
}
