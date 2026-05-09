<?php
session_start();
require_once(__DIR__ . '/../config/functions.php');
require_once(__DIR__ . '/../config/connexion.php');

if (!isset($_SESSION['id_user'])) {
    redirect('../auth/login.php', 'error', 'Vous devez vous connecter.');
}

$stmt = $pdo->prepare('SELECT role FROM UTILISATEUR WHERE id_user = ?');
$stmt->execute([$_SESSION['id_user']]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    redirect('../auth/login.php', 'error', 'Utilisateur introuvable.');
}

$_SESSION['role'] = $currentUser['role'];

if ($currentUser['role'] === 'admin') {
    redirect('admin.php');
}


include(__DIR__ . '/../headers/header.php');
?>

<main class="px-4 py-10 md:py-16">
    <section class="container mx-auto">
        <div class="rounded-[38px] border border-[#CBB59D] bg-[#F7F3EE] px-6 py-10 md:px-10 md:py-12">
            <p class="font-hatton text-sm uppercase tracking-[0.3em] text-main">Espace client</p>
            <h1 class="mt-3 font-hatton text-4xl text-main">Bienvenue sur votre dashboard</h1>
            <p class="mt-4 font-hatton text-main">
                Vous êtes connecté avec l'email : <?= htmlspecialchars($_SESSION['email']) ?>
            </p>
        </div>
    </section>
</main>

<?php
include(__DIR__ . '/../headers/footer.php');
?>
