const captchaPuzzle = document.getElementById('captcha-puzzle');
const captchaOrder = document.getElementById('captcha-order');
let premierePiece = null;

function sauvegarderOrdreCaptcha() {
    let pieces = document.querySelectorAll('.captcha-piece');
    let ordre = [];

    pieces.forEach(function(piece) {
        ordre.push(piece.dataset.piece);
    });

    captchaOrder.value = ordre.join(',');
}

function echangerPieces(pieceA, pieceB) {
    let placeA = document.createElement('span');
    let placeB = document.createElement('span');

    pieceA.parentNode.insertBefore(placeA, pieceA);
    pieceB.parentNode.insertBefore(placeB, pieceB);
    placeA.parentNode.insertBefore(pieceB, placeA);
    placeB.parentNode.insertBefore(pieceA, placeB);
    placeA.remove();
    placeB.remove();
}

if (captchaPuzzle && captchaOrder) {
    document.querySelectorAll('.captcha-piece').forEach(function(piece) {
        piece.addEventListener('click', function() {
            if (premierePiece === null) {
                premierePiece = piece;
                piece.classList.add('ring-4', 'ring-[#B09882]');
                return;
            }

            echangerPieces(premierePiece, piece);
            premierePiece.classList.remove('ring-4', 'ring-[#B09882]');
            premierePiece = null;
            sauvegarderOrdreCaptcha();
        });
    });

    sauvegarderOrdreCaptcha();
}
