<?php function currentPage()
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return basename($path ?: '');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAESKIN</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="/PA/assets/css/font.css">
</head>

<body class="bg-default">
    <nav class="relative bg-default border-b border-div">
        <div class="container mx-auto px-4 py-1 ">
            <div class="flex items-center justify-between h-16 px-4">
                <div class="flex items-center">
                    <a href="index.php" class="font-hatton text-2xl font-bold ">KAESKIN</a>
                </div>

                <div class="flex items-center gap-1">
                    <a href="rdv.php"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "rdv.php" ? 'text-[#B09882]' : '' ?>">Services</a>
                    <a href="shop.php"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "shop.php" ? 'text-[#B09882]' : '' ?>">Shop</a>
                    <a href="blog.php"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "blog.php" ? 'text-[#B09882]' : '' ?>">Blog</a>
                    <a href="login.php"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "login.php" ? 'text-[#B09882]' : '' ?>">Login</a>
                    <a href="inscription.php"
                        class="hover:text-[#B09882] transition-colors px-3 py-2 rounded-md text-sm font-hatton font-medium <?= currentPage() === "inscription.php" ? 'text-[#B09882]' : '' ?>">
                        Sign Up
                    </a>
                    <a href="panier.php"
                        class="ml-2 flex h-11 w-11 items-center justify-center rounded-full border border-div bg-[#F5F2ED] transition-all duration-300 hover:scale-105 hover:bg-button/60 <?= currentPage() === "shop.php" || currentPage() === "panier.php" ? 'ring-2 ring-[#B09882]/40' : '' ?>"
                        aria-label="Voir le panier">
                        <img src="/PA/assets/images/cart.svg" alt="Panier" class="h-6 w-6">
                    </a>

                </div>

            </div>
        </div>
    </nav>
