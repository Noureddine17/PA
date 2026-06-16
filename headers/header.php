<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$timeout = 1 * 60; 

if (isset($_SESSION['id_user'])) {
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();

        header('Location: /auth/login.php?error=session_expired');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

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

if ($isConnected) {
    $stmt = $pdo->prepare('SELECT role FROM UTILISATEUR WHERE id_user = ?');
    $stmt->execute([$_SESSION['id_user']]);
    $currentUser = $stmt->fetch();

    if ($currentUser) {
        $_SESSION['role'] = $currentUser['role'];
    }
}

$dashboardLink = 'dashboards/client.php';

if (($_SESSION['role'] ?? '') === 'admin') {
    $dashboardLink = 'dashboards/admin.php';
} elseif (($_SESSION['role'] ?? '') === 'expert') {
    $dashboardLink = 'dashboards/expert.php';
}
$dashboardPage = basename($dashboardLink);
$userName = $_SESSION['user_name'] ?? $_SESSION['email'] ?? '';
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
        <div class="container mx-auto px-4 py-1 ">
            <div class="flex items-center justify-between h-16 px-4">
                <div class="flex items-center">
                    <a href="<?= url('index.php') ?>" class="font-hatton text-2xl font-bold ">KAESKIN</a>
                </div>

                <div class="flex items-center gap-1">
                    <a href="<?= url('pages/rdv.php') ?>"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "rdv.php" ? 'text-[#B09882]' : '' ?>">Services</a>
                    <a href="<?= url('pages/shop.php') ?>"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "shop.php" ? 'text-[#B09882]' : '' ?>">Shop</a>
                    <a href="<?= url('pages/blog.php') ?>"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "blog.php" ? 'text-[#B09882]' : '' ?>">Blog</a>
                    <?php if ($isConnected): ?>
                        <a href="<?= url($dashboardLink) ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === $dashboardPage ? 'text-[#B09882]' : '' ?>"><?= htmlspecialchars($userName) ?></a>
                        <a href="<?= url('auth/deconnexion.php') ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium">Déconnexion</a>
                    <?php else: ?>
                        <a href="<?= url('auth/login.php') ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "login.php" ? 'text-[#B09882]' : '' ?>">Login</a>
                        <a href="<?= url('auth/inscription.php') ?>"
                            class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "inscription.php" ? 'text-[#B09882]' : '' ?>">
                            Sign Up
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('pages/panier.php') ?>"
                        class="ml-2 flex h-11 w-11 items-center justify-center rounded-full border border-div bg-[#F5F2ED] transition-all duration-300 hover:scale-105 <?= currentPage() === "shop.php" || currentPage() === "panier.php" ? 'ring-2 ring-[#B09882]/40' : '' ?>"
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
