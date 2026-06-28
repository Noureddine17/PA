<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

$timeout = 5 * 60; 

if (isset($_SESSION['id_user'])) {
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();

        redirect('/PA/auth/login.php', 'error', 'Session expirée.');
    }
    $_SESSION['last_activity'] = time();
}

if (isset($_SESSION['id_user'])) {
    $stmt = $pdo->prepare('UPDATE UTILISATEUR SET derniere_activite = NOW() WHERE id_user = ?');
    $stmt->execute([$_SESSION['id_user']]);
}

function currentPage()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return basename($path ?: '');
}

$baseUrl = '';

function url($path)
{
    global $baseUrl;
    return $baseUrl . '/' . ltrim($path, '/');
}

$isConnected = isset($_SESSION['id_user']);

$currentRole = $isConnected ? getCurrentRole($pdo) : null;

$dashboardLink = 'dashboards/client.php';

if ($currentRole === 'admin') {
    $dashboardLink = 'dashboards/admin.php';
} elseif ($currentRole === 'expert') {
    $dashboardLink = 'dashboards/expert.php';
}
$dashboardPage = basename($dashboardLink);
$userName = '';
if ($isConnected && isset($_SESSION['prenom'], $_SESSION['nom'])) {
    $userName = trim($_SESSION['prenom'] . ' ' . $_SESSION['nom']);
}
$alert = getAlert();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAESKIN</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script>
        window.APP_BASE_URL = '<?= $baseUrl ?>';
    </script>
    <link rel="stylesheet" href="<?= url('assets/css/font.css') ?>">
</head>

<body class="bg-default">
    <nav class="relative bg-default border-b border-div">
        <div class="container mx-auto px-4 py-1">
            <div class="flex min-h-16 items-center justify-between gap-4 px-1 sm:px-4">
                <div class="flex items-center">
                    <a href="<?= url('index.php') ?>" class="font-hatton text-2xl font-bold ">KAESKIN</a>
                </div>

                <button type="button" id="menu-mobile-button"
                    class="relative flex h-11 w-11 items-center justify-center rounded-full border border-div bg-[#F5F2ED] md:hidden"
                    aria-label="Ouvrir le menu" aria-expanded="false">
                    <span class="block h-0.5 w-5 bg-[#3C3C3B]"></span>
                    <span class="absolute block h-0.5 w-5 translate-y-2 bg-[#3C3C3B]"></span>
                    <span class="absolute block h-0.5 w-5 -translate-y-2 bg-[#3C3C3B]"></span>
                </button>

                <div id="menu-mobile"
                    class="absolute left-4 right-4 top-[72px] z-40 hidden flex-col gap-2 rounded-[28px] border border-div bg-[#F5F2ED] p-4 shadow-xl/20 md:static md:z-auto md:flex md:flex-row md:items-center md:gap-1 md:rounded-none md:border-0 md:bg-transparent md:p-0 md:shadow-none">
                    <a href="<?= url('pages/rdv.php') ?>"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-base md:text-sm font-hatton font-medium <?= currentPage() === "rdv.php" ? 'text-[#B09882]' : '' ?>">Services</a>
                    <a href="<?= url('pages/shop.php') ?>"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-base md:text-sm font-hatton font-medium <?= currentPage() === "shop.php" ? 'text-[#B09882]' : '' ?>">Shop</a>
                    <a href="<?= url('pages/blog.php') ?>"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-base md:text-sm font-hatton font-medium <?= currentPage() === "blog.php" ? 'text-[#B09882]' : '' ?>">Blog</a>
                    <?php if ($isConnected): ?>
                        <a href="<?= url($dashboardLink) ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-base md:text-sm font-hatton font-medium <?= currentPage() === $dashboardPage ? 'text-[#B09882]' : '' ?>"><?= htmlspecialchars($userName) ?></a>
                        <a href="<?= url('auth/deconnexion.php') ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-base md:text-sm font-hatton font-medium">Déconnexion</a>
                    <?php else: ?>
                        <a href="<?= url('auth/login.php') ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-base md:text-sm font-hatton font-medium <?= currentPage() === "login.php" ? 'text-[#B09882]' : '' ?>">Login</a>
                        <a href="<?= url('auth/inscription.php') ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-base md:text-sm font-hatton font-medium <?= currentPage() === "inscription.php" ? 'text-[#B09882]' : '' ?>">
                            Sign Up
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('pages/panier.php') ?>"
                        class="mt-2 flex h-11 w-11 items-center justify-center rounded-full border border-div bg-[#F5F2ED] transition-all duration-300 hover:scale-105 md:ml-2 md:mt-0 <?= currentPage() === "shop.php" || currentPage() === "panier.php" ? 'ring-2 ring-[#B09882]/40' : '' ?>"
                        aria-label="Voir le panier">
                        <img src="<?= url('assets/images/cart.svg') ?>" alt="Panier" class="h-6 w-6">
                    </a>

                </div>

            </div>
        </div>
    </nav>

    <?php if ($alert): ?>
        <?php if ($alert['type'] === 'success'): ?>
            <div class="container mx-auto mt-6 px-4">
                <div class="rounded-full border border-green-200 bg-green-50 px-5 py-3 font-hatton text-green-700">
                    <?= htmlspecialchars($alert['message']) ?>
                </div>
            </div>
        <?php else: ?>
            <div class="container mx-auto mt-6 px-4">
                <div class="rounded-full border border-red-200 bg-red-50 px-5 py-3 font-hatton text-red-700">
                    <?= htmlspecialchars($alert['message']) ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        let boutonMenuMobile = document.getElementById('menu-mobile-button');
        let menuMobile = document.getElementById('menu-mobile');

        if (boutonMenuMobile && menuMobile) {
            boutonMenuMobile.addEventListener('click', function() {
                let menuOuvert = !menuMobile.classList.contains('hidden');
                menuMobile.classList.toggle('hidden');
                boutonMenuMobile.setAttribute('aria-expanded', String(!menuOuvert));
            });
        }
    </script>
