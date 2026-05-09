const captchaRange = document.getElementById('captcha-range');
const captchaPiece = document.getElementById('captcha-piece');
const captchaPosition = document.getElementById('captcha-position');

function bougerPieceCaptcha() {
    let position = captchaRange.value;

    captchaPiece.style.left = position + 'px';
    captchaPosition.value = position;
}

if (captchaRange && captchaPiece && captchaPosition) {
    captchaRange.addEventListener('input', bougerPieceCaptcha);
    bougerPieceCaptcha();
}
